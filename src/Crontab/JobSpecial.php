<?php
/**
 * Crontab / JobSpecial.php
 *
 * @author Geoff Davis <gef.davis@gmail.com>
 */

namespace Crontab;

/**
 * Class JobSpecial
 *
 * @package Crontab
 */
class JobSpecial extends Job
{
    /** @var string */
    protected $special;

    /**
     * @var array Of special words that used in place of the time/date attributes
     */
    public static $_specials = array(
        '@reboot',
        '@yearly',
        '@annually',
        '@monthly',
        '@weekly',
        '@daily',
        '@midnight',
        '@hourly'
    );


    /**
     * @param string $special
     * @return JobSpecial
     */
    public function setSpecial($special)
    {
        $this->special = $special;

        return $this;
    }

    /**
     * @return string
     */
    public function getSpecial()
    {
        return $this->special;
    }

    /**
     * @return JobSpecial
     */
    public function generateHash()
    {
        $this->hash = hash('md5', serialize(array(
            strval($this->getSpecial()),
            strval($this->getCommand()),
        )));

        return $this;
    }

    /**
     * Get an array of job entries
     *
     * @return array
     */
    public function getEntries()
    {
        return array(
            $this->getSpecial(),
            $this->getCommand(),
            $this->prepareLog(),
            $this->prepareError(),
            $this->prepareComments(),
        );
    }
}
