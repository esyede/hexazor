<?php

namespace System\Debugger;

defined('DS') or exit('No direct script access allowed.');

use System\Debugger\Interfaces\BarPanelInterface;

class DefaultBarPanel implements BarPanelInterface
{
    private $id;

    public $data;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getTab()
    {
        ob_start(function () {
        });

        $data = $this->data;
        require_once __DIR__.'/assets/bar/'.$this->id.'.tab.php';

        return ob_get_clean();
    }

    public function getPanel()
    {
        ob_start(function () {
        });

        $panel = __DIR__.'/assets/bar/'.$this->id.'.panel.php';

        if (is_file($panel)) {
            $data = $this->data;
            require_once $panel;
        }

        return ob_get_clean();
    }
}
