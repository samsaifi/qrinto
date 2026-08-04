<?php

namespace App\Http\Controllers;

class CartPcController extends CartController
{
    protected function getViewPath($viewName)
    {
        return "quick-flow-pc.{$viewName}";
    }

    protected function getRoutePrefix()
    {
        return 'flow-pc.';
    }
}
