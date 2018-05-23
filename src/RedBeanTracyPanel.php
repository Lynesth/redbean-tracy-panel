<?php

namespace RedBeanTracyPanel;

use RedBeanPHP\Facade as R;
use Tracy\Debugger;
use Tracy\IBarPanel;

class Panel implements IBarPanel
{
    /**
     * Base64 icon for Tracy panel.
     * @var string
     * @see http://www.flaticon.com/free-icon/coffee-bean_63156
     * @author Freepik.com
     * @license http://file000.flaticon.com/downloads/license/license.pdf
     */
    public static $icon = 'data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTYuMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgd2lkdGg9IjE2cHgiIGhlaWdodD0iMTZweCIgdmlld0JveD0iMCAwIDUwMS43NTIgNTAxLjc1MiIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgNTAxLjc1MiA1MDEuNzUyOyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+CjxnPgoJPHBhdGggZD0iTTQ0MC43MjMsMTg0LjA2MWMtODguMS05NC45LTI2NS42LTE3Mi0zOTYuNi0xNDQuNGMtOC42LDEuOC0xMi4yLDguNi0xMSwxNS4zYy04MS40LDQ0LjcsOC42LDE5OS41LDQyLjgsMjQ0LjggICBjNjcuMyw5MCwxODcuODk5LDE5MC44OTksMzA5LjEsMTYzLjM5OWM1NS43LTEyLjg5OSw5Ni43LTY1LjUsMTEyLTExOC4xQzUxNC43MjMsMjg1LjA2MSw0NzkuODIzLDIyNi4zNjEsNDQwLjcyMywxODQuMDYxeiAgICBNNDMuNTIzLDgxLjI2MWMxLjItMy4xLDIuNC02LjEsMy43LTkuOGM3OC45LDE0MiwyMzYuOCwyNTIuMSwzODguNiwzMDEuMWMxLjgwMSwwLjYsMy4xMDEsMC42LDQuMzAxLDAuNmMwLDEuMiwwLDIuNCwwLDMuNyAgIEMyNzEuNzIzLDM1Ny44NjEsMTIxLjgyMywyMjYuMzYxLDQzLjUyMyw4MS4yNjF6IiBmaWxsPSIjODcyNTIwIi8+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPC9zdmc+Cg==';

    /**
     * Title for Tracy panel.
     * @var string
     */
    private $title = 'RedBean Queries Log';

    /**
     * Whether to show or not '--keep-cache' in your queries.
     * @var boolean
     */
    private $showKeepCache = false;
    
    /**
     * SQL highlighter function to replace RedBean's default one.
     * @var callable|null
     */
    private $highlighter;

    /**
     * Logged queries
     * @var array
     */
    private $queries;

    public function __construct($database) {
        $this->database = $database;
        R::selectDatabase($this->database);
        R::debug(true, 3);
    }

    /**
     * Collect all the executed queries.
     */
    protected function collect()
    {
        R::selectDatabase($this->database);
        $output = R::getLogger()->grep(' ');

        $queries = [];

        foreach ($output as $key => $value) {
            if (substr($value, 0, 9) == 'resultset') {
                unset($output[$key]);
            } else {
                if ($this->showKeepCache) {
                    $queries[] = $value;
                } else {
                    $queries[] = str_replace('-- keep-cache', '', $value);
                }
            }
        }

        $this->queries = $queries;
    }

    /**
     * Renders HTML code for custom tab.
     *
     * @return string
     */
    public function getTab()
    {
        $this->collect();
        $html = '<img src="' . self::$icon . '" alt="RedBeanPHP queries logger for Tracy"/> ';
        $queries = count(array_filter($this->queries, function($q) { return strpos($q, '--') !== 0; }));
        if ($queries == 1) {
            $html .= '1 query';
        } else {
            $html .= $queries . ' queries';
        }
        return $html;
    }

    /**
     * Renders HTML code for custom panel.
     *
     * @return string
     */
    public function getPanel()
        {
        $queries = $this->queries;
        $html = '<h1>' . $this->title . '</h1>';
        $html .= '<div class="tracy-inner tracy-InfoPanel"><table width="300">';
        foreach ($queries as $query) {
            if (is_callable($this->highlighter)) {
                $query = preg_replace('#(<b style=\"color:green\">)|(</b>)#', '', $query);
                $html .= '<tr><td>' . call_user_func($this->highlighter, $query) . '</td></tr>';
            } else {
                $html .= '<tr><td>' . $query . '</td></tr>';
            }
        }
        $html .= '</table></div>';

        return $html;
    }
    
    /**
     * Sets the title of the panel in Tracy.
     *
     * @var string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }
    
    /**
     * Sets whether the queries should hide '-- keep-cache' or not
     * Set to true by default.
     *
     * @param bool $yesNo 
     */
    public function hideKeepCache($yesNo)
    {
        $this->showKeepCache = !$yesNo;
    }
    
    /**
     * Sets the function to be used to highlight SQL queries in the panel
     * It will automatically remove the green highlighting that RedBean does
     * Will take a string as parameter and expects html string as output.
     *
     * @param callable $highlight The function to be used
     */
    public function setHighlighter($highlighter)
    {
        $this->highlighter = $highlighter;
    }
}
