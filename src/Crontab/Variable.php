<?php
/**
 * @package Crontab
 */

namespace Crontab;

class Variable
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $value;

    /**
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->render();
        } catch (\Exception $exception) {
            return '# ' . $exception;
        }
    }

    /**
     * Parse a variable line
     * 
     * @param string $varLine
     * @return Variable
     */
    public static function parse($varLine)
    {
        $parts = explode('=', $varLine, 2);
        if (!$parts || count($parts) !== 2) {
            throw new \InvalidArgumentException("Line does not appear to contain a variable");
        }

        $variable = new Variable();
        $variable->setName(trim(array_shift($parts)))
            ->setValue(trim(array_shift($parts)));

        return $variable;
    }

    /**
     * @return string
     */
    public function render()
    {
        return $this->getName() . '=' . $this->getValue();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $name
     * @return Variable
     * @throws \InvalidArgumentException if the variable name contain spaces or a $
     */
    public function setName($name)
    {
        if (strpos($name, ' ') !== false) {
            throw new \InvalidArgumentException("Variable names cannot contain spaces");
        } else if (strpos($name, '$') !== false) {
            throw new \InvalidArgumentException("Variable names cannot contain a '\$' character");
        }
        $this->name = $name;

        return $this;
    }

    /**
     * @param string $value
     * @return Variable
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return hash('md5', serialize(array(
            strval($this->getName()),
            strval($this->getValue())
        )));
    }
}
