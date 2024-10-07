<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
Namespace Snap\util;

Use Snap\InputException;
Use Snap\IEntity;

/**
 * Implements a calculator methods that will automatically implement mathematical
 * calculation with floating point in specific way as required by different
 * merchants.
 */
class Calculator
{
    Use \Snap\TLogging;

    private $numDecimals = 5;
    private $useRounding = true;
    private $roundResultOnly = true;

    /**
     * Constructor to create a calculator instance
     * @param integer $keepNumDecial   Number of decimals to keep in calculation
     * @param boolean $useRounding     Employ round the result or chopping result off.
     * @param boolean $roundResultOnly Just do the rounding on the results calculated and not on input.
     */
    public function __construct($keepNumDecimal = 5, $useRounding = true, $roundResultOnly = true)
    {
        $this->numDecimals = $keepNumDecimal;
        $this->useRounding = $useRounding;
        $this->roundResultOnly = $roundResultOnly;
    }

    /**
     * All calculator methods are round(), add(), plus(), sub(), subtract(), minus(), times(), multiply(), divide(), div(), divideby()
     * Only accept 2 arguments for all the functions.  I.e.  2 numbers to do the operation.
     */
    public function __call($name, $arguments) {
        if(2 < count($arguments) || 1 > count($arguments)) {
            throw new \Snap\InputException(__CLASS__.' only support 2 arguments for its functions');
        }
        $name = strtolower($name);
        switch($name) {
            case 'round':
                $this->initMathFunction();
                return $this->normalise(bcadd(0, $arguments[0]));
                break;
            case 'add':
            case 'plus':
                return $this->doMathOperation('bcadd', $arguments[0], $arguments[1]);
                break;
            case 'subtract':
            case 'sub':
            case 'minus':
                return $this->doMathOperation('bcsub', $arguments[0], $arguments[1]);
                break;
            case 'times':
            case 'multiply':
                return $this->doMathOperation('bcmul', $arguments[0], $arguments[1]);
                break;
            case 'div':
            case 'divide':
            case 'divideby':
                return $this->doMathOperation('bcdiv', $arguments[0], $arguments[1]);
                break;
        }
    }

    private function doMathOperation($method, $arg1, $arg2)
    {
        $this->initMathFunction();
        if($this->roundResultOnly) {
            $result = call_user_func($method, $arg1, $arg2);
        } else {
            $result = call_user_func($method, $this->normalise($arg1), $this->normalise($arg2));
        }
        return $this->normalise($result);
    }
    private function initMathFunction()
    {
        if($this->useRounding) {
            bcscale($this->numDecimals + 2);
        } else {
            bcscale($this->numDecimals);
        }        
    }

    private function normalise($result)
    {
        if(!$this->useRounding) {
            return $result;
        }
        $multiplier = '1' . str_repeat('0', $this->numDecimals);
        $computeResult = bcmul($result, $multiplier);
        if(preg_match('/^([+-]?[0-9]+)\.([0-9]+)$/', $computeResult, $matches)) {
            if(5 > intval($matches[2][0])) {
                return bcdiv($matches[1],$multiplier);
            } else {
                if ($matches[1] > 0){
                    // positive
                    return bcdiv(bcadd($matches[1], 1), $multiplier);
                }elseif ($matches[1] < 0){
                    // negative
                    return bcdiv(bcsub($matches[1], 1), $multiplier);
                }else{
                    // ZERO with -0 AND 0 on $matches[1]
                    if ($matches[0] > 0){
                        return bcdiv(bcadd($matches[1], 1), $multiplier);
                    }
                    if ($matches[0] < 0){
                        return bcdiv(bcsub($matches[1], 1), $multiplier);
                    }
                }
            }
        }
        return $result;
    }
}