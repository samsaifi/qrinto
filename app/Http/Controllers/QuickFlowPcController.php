<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QuickFlowPcController extends QuickFlowController
{
    /**
     * Always use the PC view folder.
     */
    protected function getViewPath($viewName)
    {   
        // @
        return "quick-flow-pc.{$viewName}";
    }

    /**
     * Always use the PC route prefix.
     */
    protected function getRoutePrefix()
    {
        return 'flow-pc.';
    }
}
