<?php

namespace System\Debugger;

defined('DS') or exit('No direct script access allowed.');

use System\Debugger\Interfaces\BarPanelInterface;

class Bar
{
    public $info = [];

    private $panels = [];

    /**
     * Tambahkan panel baru.
     *
     * @param BarPanelInterface $panel
     * @param string            $id
     */
    public function addPanel(BarPanelInterface $panel, $id = null)
    {
        if (null === $id) {
            $c = 0;
            do {
                $id = get_class($panel).($c++ ? "-$c" : '');
            } while (isset($this->panels[$id]));
        }

        $this->panels[$id] = $panel;

        return $this;
    }

    /**
     * Mereturn panel berdasarkan id yang diberikan.
     *
     * @param string $id
     *
     * @return BarPanleInterface
     */
    public function getPanel($id)
    {
        return isset($this->panels[$id]) ? $this->panels[$id] : null;
    }

    /**
     * Render debug bar.
     */
    public function render()
    {
        // Error: Cannot send session cache limiter - headers already sent
        // if app.config.debugger.scream is set to TRUE.
        // @session_start();

        $session = &$_SESSION['__DEBUGGER']['__InternalDebugger'];
        $redirect = preg_match('#^Location:#im', implode("\n", headers_list()));

        if ($redirect) {
            Dumper::fetchLiveData();
            Dumper::$livePrefix = count($session).'p';
        }

        $obLevel = ob_get_level();
        $panels = [];

        foreach ($this->panels as $id => $panel) {
            $idHtml = preg_replace('#[^a-z0-9]+#i', '-', $id);

            try {
                $tab = (string) $panel->getTab();
                $panelHtml = $tab ? (string) $panel->getPanel() : null;
                $panels[] = ['id' => $idHtml, 'tab' => $tab, 'panel' => $panelHtml];
            } catch (\Throwable $e) {
            } catch (\Exception $e) {
            }

            if (isset($e)) {
                $panels[] = [
                    'id' => "error-$idHtml",
                    'tab' => "Error in $id",
                    'panel' => '<h1>Error: '.$id.'</h1><div class="debugger-inner">'
                        .nl2br(htmlspecialchars($e, ENT_IGNORE, 'UTF-8')).'</div>',
                ];

                while (ob_get_level() > $obLevel) {
                    ob_end_clean();
                }
            }
        }

        if ($redirect) {
            $session[] = [
                'panels' => $panels,
                'liveData' => Dumper::fetchLiveData(),
            ];

            return;
        }

        $liveData = Dumper::fetchLiveData();
        foreach (array_reverse((array) $session) as $reqId => $info) {
            $panels[] = [
                'tab' => '<span title="Previous request before redirect">previous</span>',
                'panel' => null,
                'previous' => true,
            ];

            foreach ($info['panels'] as $panel) {
                $panel['id'] .= '-'.$reqId;
                $panels[] = $panel;
            }

            $liveData += $info['liveData'];
        }

        $session = null;
        require __DIR__.DS.'assets'.DS.'bar'.DS.'bar.php';
    }
}
