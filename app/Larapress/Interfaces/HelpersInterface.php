<?php namespace Larapress\Interfaces;

interface HelpersInterface {

    /**
     * Sets the page title (Shares the title variable for the view)
     *
     * @param string $page_name The page name
     * @return void
     */
    public function setPageTitle($page_name);

    /**
     * Writes performance related statistics into the log file
     *
     * @return void
     */
    public function logPerformance();

}
