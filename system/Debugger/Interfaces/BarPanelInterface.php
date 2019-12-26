<?php

namespace System\Debugger\Interfaces;

defined('DS') or exit('No direct script access allowed.');

interface BarPanelInterface
{
    public function getTab();

    public function getPanel();
}
