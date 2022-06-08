<?php

//use JetBrains\PhpStorm\ArrayShape;

//#[ArrayShape(['recursive' => "bool|null", 'img_name' => "false|mixed|string", 'sheet_name' => "false|mixed|string", 'directory' => "mixed|null|string"])]
function getInstruction(): array
{
    global $argc;
    global $argv;
    $instructions = [
        'recursive' => NULL,
        'img_name' => 'sprite.png',
        'sheet_name' => 'style.css',
        'directory' => '',
    ];

    if ($argv === 2) {

        $help = getopt('h', ['help']);
        if (array_key_exists('h', $help) || array_key_exists('help', $help)) {
            man:

            echo "\nHELP MANUAL :\n\n";
            echo("    -h, --help\n            Display help.\n\n    -r, --recursive\n            Look for images into the assets_folder passed as arguement and all of its subdirectories.\n\n    -i, --output-image=IMAGE
            Name of the generated image. If blank, the default name is « sprite.png ».\n\n    -s, --output-style=STYLE
            Name of the generated stylesheet. If blank, the default name is « style.css ».\n\n ");
        }
    }

    if ($argv === 1) {
        goto man;
    } else if ($argv > 2) {
        $opt = getopt('hri:s:', ['help', 'recursive', 'output-image:', 'output-style:']);

        if ($opt > 0) {
            // Print the manual
            if (array_key_exists('h', $opt) || array_key_exists('help', $opt)) {
                goto man;
            }

            if (array_key_exists('r', $opt) || array_key_exists('recursive', $opt)) {
                $instructions['recursive'] = true;
            }

            if (array_key_exists('i', $opt) || array_key_exists('output-image', $opt)) {
                $val = array_key_exists('i', $opt) ? $opt['i'] : $opt['output-image'];
                if (!str_ends_with($val, '.png')) {
                    $val .= '.png';
                }
                $instructions['img_name'] = $val;
            }

            if (array_key_exists('s', $opt) || array_key_exists('output-style', $opt)) {
                $val = array_key_exists('s', $opt) ? $opt['s'] : $opt['output-style'];
                if (!str_ends_with($val, '.css')) {
                    $val .= '.css';
                }
                $instructions['sheet_name'] = $val;
            }
            if(str_starts_with($instructions['img_name'],"-")){
                $instructions['img_name'] = "sprite.png";

            }
            if(str_starts_with($instructions['sheet_name'],"-")){
                $instructions['sheet_name'] = "style.css";
            }
            $nameimg = $instructions['img_name'];
            $namecss = $instructions['sheet_name'];
            $extension = '.png';

            $input = $argc > 1 ? $argv[$argc - 1] : NULL;

            $nouv = $input;
            $nouv.= $extension;


            if($nameimg === $nouv){
                $instructions['img_name'] = "sprite.png";
            }
            if($namecss === $nouv){
                $instructions['sheet_name'] = "style.css";
            }

            if (is_dir($input)) {
                $instructions['directory'] = $input;
            }

            try {

                if (!is_dir($input) || empty($input)) {
                    throw new Exception ("\e[91mveuillez Spécifier un chemin valide\n");
                }
            } catch (Exception $e) {
                $message = $e->getMessage();
                die($message);
            }
        }
    }

    return $instructions;
}

getInstruction();

function arbo($racine): array
{
   static $scanner = array();
    $liste = glob($racine . "/*");
    static $resultat = array();
    foreach ($liste as $element) {
        $scanner[] = $element;
        if (filetype($element) === "dir") {
            arbo($element);
        } elseif (filetype($element) === "file") {
            $resultat[] = $element;

        }
    }

    return $resultat;
}


function get_png($chemin): array
{
    $file = arbo($chemin);
    $my_png = [];
    foreach ($file as $png) {
        if (fnmatch("*.png", $png)) {
            $my_png[] = $png;
        }
    }
    return $my_png;
}

function csheet()
{
    global $folder;
    global $long;
    global $larg;
    global $fileList;
    global $largeurtotal;
    global $hauteurtotal;
    $tab = getInstruction();
    $folder = $tab['directory'];
    $reccure = $tab['recursive'];
    $hauteurtotal = 0;
    $largeurtotal = 0;

    if ($reccure !== NULL) {
        $fileList = get_png($folder);
    }
    else {

        $life = arbo($folder);
        print_r($life);
        exit();
    }

    $max_size = max(array_keys($fileList));
    $max_file = $fileList[$max_size];
    $stat = getimagesize($max_file);
    $larg = $stat[0];
    $long = $stat[1];

    foreach ($fileList as $value) {
        $infos = getimagesize($value);
        $largeurtotal += $infos[0];
        $hauteurtotal += $infos[1];

    }

    if ($larg > $long) {
        spritevertical();
        cssGenV();
    } else {
        spriteElse();
        cssGenE();
    }

}

csheet();

function spriteVertical()
{
    global $folder;
    global $larg;
    global $fileList;
    global $hauteurtotal;
    $tab = getInstruction();
    $namesprite1 = $tab['img_name'];
    $nouvelleImage = imagecreatetruecolor($larg, $hauteurtotal);
    imagealphablending($nouvelleImage,false);
    $black = imagecolorallocate($nouvelleImage,255,255,255);
    imagefill($nouvelleImage,0,0,$black);
    imagecolortransparent($nouvelleImage,$black);
    imagesavealpha($nouvelleImage,true);
    $y = 0;
    foreach ($fileList as $v) {
        $imagesource = imagecreatefrompng($v);
        $info1 = getimagesize($v);
        $larg1 = $info1[0];
        $haut1 = $info1[1];
        imagecopymerge($nouvelleImage, $imagesource, 0, $y, 0, 0, $larg1, $haut1, 100);
        $y += $haut1;
    }
    imagepng($nouvelleImage, $folder.'/'. $namesprite1);
    imagedestroy($nouvelleImage);
}


function spriteElse()
{
    $tab = getInstruction();
    $namesprite1 = $tab['img_name'];

    global $folder;
    global $fileList;
    global $long;
    global $largeurtotal;
    $nouvelleImage = imagecreatetruecolor($largeurtotal, $long);
    $black = imagecolorallocate($nouvelleImage,255,255,255);
    imagefill($nouvelleImage,0,0,$black);
    imagecolortransparent($nouvelleImage,$black);
    imagealphablending($nouvelleImage,false);
    imagesavealpha($nouvelleImage,true);
    $x = 0;
    foreach ($fileList as $v) {
        $imagesource = imagecreatefrompng($v);
        $info2 = getimagesize($v);
        $larg2 = $info2[0];
        $haut2 = $info2[1];
        imagecopy($nouvelleImage, $imagesource, $x, 0, 0, 0, $larg2, $long);
        $x += $larg2;
    }
    imagepng($nouvelleImage, $folder.'/'.$namesprite1);
    imagedestroy($nouvelleImage);

}

function cssGenV()
{
    $tab = getInstruction();
    $namesprite1 = $tab['img_name'];
    $namecss1 = $tab['sheet_name'];
    global $fileList;
    $x2 = 0;
    $css1 = "/* Generated by Majid */\n";
    $css1 .= '.sprite {
background-image: url(jade/' . $namesprite1 . ');
background-repeat: no-repeat;
display: block;
}' . PHP_EOL;

    foreach ($fileList as $item2) {
        $name2 = basename($item2, ".png");
        $info4 = getimagesize($item2);
        $larg4 = $info4[0];
        $haut4 = $info4[1];
        $css1 .= '.pic-' . $name2 . ' {
    width: ' . $larg4 . 'px;
    height: ' . $haut4 . 'px;
    background-position: 0 -' . $x2 . 'px;
}' . PHP_EOL;

        $x2 += $haut4;
    }
    file_put_contents($namecss1, $css1);
}

function  cssGenE()
{
    $tab = getInstruction();
    $namesprite1 = $tab['img_name'];
    $namecss1 = $tab['sheet_name'];
    global $fileList;
    $x2 = 0;
    $css1 = "/* Generated by Majid */\n";
    $css1 .= '.sprite {
background-image: url(jade/' . $namesprite1 . ');
background-repeat: no-repeat;
display: inline-block;
}' . PHP_EOL;

    foreach ($fileList as $item2) {
        $info2 = getimagesize($item2);
        $larg2 = $info2[0];
        $name2 = basename($item2, ".png");
        $info4 = getimagesize($item2);
        $larg4 = $info4[0];
        $haut4 = $info4[1];
        $css1 .= '.pic-' . $name2 . ' {
    width: ' . $larg4 . 'px;
    height: ' . $haut4 . 'px;
    background-position: -' . $x2 . 'px 0;
}' . PHP_EOL;
        $x2 += $larg2;
    }
    file_put_contents($namecss1, $css1);
}



