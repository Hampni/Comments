<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Gregwar\Captcha\CaptchaBuilder;


class CaptchaController extends Controller
{
    
    public function getCapthca()
    {
        $builder = new CaptchaBuilder;
        $builder->build();

        $captchaText = $builder->getPhrase();

        return response($builder->output())
            ->header('Content-Type', 'image/png')
            ->header('X-Captcha', $captchaText);
    }
}
