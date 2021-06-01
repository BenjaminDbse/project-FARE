<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\PasswordChangeRequestType;
use App\Form\ResetPasswordType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/connexion", name="app_login")
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/deconnexion", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/mot-de-passe-oublie", name="app_forgot_password")
     * @param Request $request
     * @param UserRepository $userRepository
     * @param TokenGeneratorInterface $tokenGenerator
     * @param MailerInterface $mailer
     * @return Response
     * @throws TransportExceptionInterface
     */
    public function forgotPassword(
        Request $request,
        UserRepository $userRepository,
        TokenGeneratorInterface $tokenGenerator,
        MailerInterface $mailer
        ): Response
    {
        $form = $this->createForm(PasswordChangeRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $user = $userRepository->findOneBy(['email' =>$data['email']]);

            if (!$user) {
                $this->addFlash('danger','Cet adresse mail n\'existe pas.');
                return $this->redirectToRoute('app_forgot_password');
            }
            $token = $tokenGenerator->generateToken();
            try {
                $user->setToken($token);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
            } catch(\Exception $e) {
                $this->addFlash('danger','Une erreur est survenue : ' . $e->getMessage());
                return $this->redirectToRoute('app_login');
            }
            $url = $this->generateUrl(
                'app_reset_password',
                ['token' => $token],
                UrlGeneratorInterface::ABSOLUTE_URL);

            $email = (new Email())
                ->from('Suppot-info@fare-sa.com')
                ->to($user->getEmail())
                ->subject('Demande de reinitialisation de mot de passe')
                ->html('<p>Une demande de réinitilisation de mot de passe à été effectuée pour l\'application TCA de FARE.</p>
                        <p>Veuillez cliquer sur le lien suivant pour réinitialiser votre mot de passe :</p>' . $url );
            $mailer->send($email);
            $this->addFlash('success',
                'Un email de réinitialisation de mot de passe à été envoyé à l\'adresse mail indiqué');
            return $this->redirectToRoute('app_login');
        }
        return $this->render('security/changeRequestPassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{token}", name="app_reset_password")
     * @param Request $request
     * @param $token
     * @param UserPasswordEncoderInterface $userPasswordEncoder
     * @return RedirectResponse|Response
     */
    public function resetPassword(Request $request,$token, UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['token' => $form->get('token')]);

        if (!$user) {
            $this->addFlash('danger', 'Token inconnu');
            return $this->redirectToRoute('app_login');
        }
        if ($request->isMethod('POST')) {
            $user->setToken(null);

            $user->setPassword($userPasswordEncoder->encodePassword($user,$request->request->get('password')));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success','Votre mot de passe a bien été modifier');
            return $this->redirectToRoute('app_login');
        } else {
            return $this->render('security/resetPassword.html.twig', ['token' => $token]);
        }
    }
}
