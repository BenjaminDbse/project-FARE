<?php

namespace App\Services;

class Slugify{

    public function generate(string $input) : string
    {
        $input = strtr(utf8_decode($input),
            utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'),
            'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
        $input = preg_replace('/[\.\,\/\#\!\$\%\\\\&\*\;\:\{\}\=\_\`\+\~\--\?\(\)]+/','',$input);
        return  str_replace(' ','-',trim(mb_strtolower($input)));
    }
}
