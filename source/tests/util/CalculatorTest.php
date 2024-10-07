<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 *
 * 
 */

use PHPUnit\Framework\TestCase;
use Snap\util\Calculator;

/**
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 */
final class CalculatorTest extends TestCase
{
    function test3DecimalRoundingCalculationMode()
    {
        //Test for all MBB usages.
        $c = new Calculator(3, true, true);

        $this->assertEquals(3.456, $c->round(3.45558));
        $this->assertEquals(3.455, $c->round(3.45542));
        $this->assertEquals(3.456, $c->round(3.45569));

        $this->assertEquals('4.582', $c->add('3.12344', '1.45821'));
        $this->assertEquals('4.582', $c->plus(3.12344543, 1.45821));
        $this->assertEquals('4.581', $c->add('3.12304', '1.45821'));
        $this->assertEquals('4.581', $c->plus(3.12304, 1.45821));
        $this->assertEquals(3, $c->add(1, 2));
        $this->assertEquals(3, $c->plus('54643590809854390584309', '-54643590809854390584306'));
        $this->assertEquals(3, $c->add('54643590809854390584309.000098', '-54643590809854390584306.000008'));
        $this->assertEquals(3.001, $c->plus('54643590809854390584309.00098', '-54643590809854390584306.00008'));

        $this->assertEquals('1.665', $c->sub('3.12344', '1.45821'));
        $this->assertEquals('1.665', $c->minus(3.12344543, 1.45821));
        $this->assertEquals('1.664', $c->sub('3.12304', '1.45881'));
        $this->assertEquals('1.664', $c->minus(3.12304, 1.45881));
        $this->assertEquals(-1, $c->sub(1, 2));
        $this->assertEquals(3, $c->minus('54643590809854390584309', '54643590809854390584306'));
        $this->assertEquals(3, $c->sub('54643590809854390584309.000098', '54643590809854390584306.000008'));
        $this->assertEquals(3.001, $c->minus('54643590809854390584309.00098', '54643590809854390584306.00008'));

        $this->assertEquals('4.554', $c->multiply('3.12344', '1.45812'));
        $this->assertEquals('4.554', $c->times(3.12344543, 1.45812));
        $this->assertEquals('4.556', $c->multiply('3.12304', '1.45881'));
        $this->assertEquals('4.556', $c->times(3.12304, 1.45881));
        $this->assertEquals(2, $c->multiply(1, 2));
        $this->assertEquals('2985922016594803213441652876530088311265254554', $c->times('54643590809854390584309', '54643590809854390584306'));
        $this->assertEquals(183506098.675, $c->multiply('30584309.000098', '6.000008'));
        $this->assertEquals(15476274486.97400, $c->times('30584309.000098', '506.02008'));

        $this->assertEquals('2.142', $c->divideby('3.12344', '1.45812'));
        $this->assertEquals('2.142', $c->div(3.12344543, 1.45812));
        $this->assertEquals('2.141', $c->divideby('3.12304', '1.45881'));
        $this->assertEquals('2.141', $c->div(3.12304, 1.45881));
        $this->assertEquals(0.5, $c->divideby(1, 2));
        $this->assertEquals('1', $c->div('54643590809854390584309', '54643590809854390584306'));
        $this->assertEquals(5097378.037, $c->divideby('30584309.000098', '6.000008'));
    }

    function test3DecimalNoRoundingCalculationMode()
    {
        //Test for all MBB usages.
        $c = new Calculator(3, false, true);

        $this->assertEquals(3.455, $c->round(3.45558));
        $this->assertEquals(3.455, $c->round(3.45542));
        $this->assertEquals(3.455, $c->round(3.45569));

        $this->assertEquals('4.581', $c->add('3.12344', '1.45821'));
        $this->assertEquals('4.581', $c->plus(3.12344543, 1.45821));
        $this->assertEquals('4.581', $c->add('3.12304', '1.45821'));
        $this->assertEquals('4.581', $c->plus(3.12304, 1.45821));
        $this->assertEquals(3, $c->add(1, 2));
        $this->assertEquals(3, $c->plus('54643590809854390584309', '-54643590809854390584306'));
        $this->assertEquals(3, $c->add('54643590809854390584309.000098', '-54643590809854390584306.000008'));
        $this->assertEquals(3, $c->plus('54643590809854390584309.00098', '-54643590809854390584306.00008'));

        $this->assertEquals('1.665', $c->sub('3.12384', '1.45821'));
        $this->assertEquals('1.665', $c->minus(3.12344543, 1.45821));
        $this->assertEquals('1.664', $c->sub('3.12304', '1.45881'));
        $this->assertEquals('1.664', $c->minus(3.12304, 1.45881));
        $this->assertEquals(-1, $c->sub(1, 2));
        $this->assertEquals(3, $c->minus('54643590809854390584309', '54643590809854390584306'));
        $this->assertEquals(3, $c->sub('54643590809854390584309.000098', '54643590809854390584306.000008'));
        $this->assertEquals(3., $c->minus('54643590809854390584309.00098', '54643590809854390584306.00008'));

        $this->assertEquals('4.558', $c->multiply('3.12444', '1.45912'));
        $this->assertEquals('4.558', $c->times(3.12444543, 1.45912));
        $this->assertEquals('4.555', $c->multiply('3.12304', '1.45881'));
        $this->assertEquals('4.555', $c->times(3.12304, 1.45881));
        $this->assertEquals(2, $c->multiply(1, 2));
        $this->assertEquals('2985922016594803213441652876530088311265254554', $c->times('54643590809854390584309', '54643590809854390584306'));
        $this->assertEquals(183506098.675, $c->multiply('30584309.000098', '6.000008'));
        $this->assertEquals(15476274486.97400, $c->times('30584309.000098', '506.02008'));

        $this->assertEquals('2.142', $c->divideby('3.12344', '1.45812'));
        $this->assertEquals('2.142', $c->div(3.12344543, 1.45812));
        $this->assertEquals('2.151', $c->divideby('3.12304', '1.45181'));
        $this->assertEquals('2.151', $c->div(3.12304, 1.45181));
        $this->assertEquals(0.5, $c->divideby(1, 2));
        $this->assertEquals('1', $c->div('54643590809854390584309', '54643590809854390584306'));
        $this->assertEquals(5097378.036, $c->divideby('30584309.000098', '6.000008'));
    }

   function test6DecimalCalculationMode()
    {
        //Test for all MBB usages.
        $c = new Calculator(6, true, true);

        $this->assertEquals('4.581654', $c->add('3.12344', '1.45821370'));
        $this->assertEquals('4.581659', $c->plus(3.12344543, 1.45821370));
        $this->assertEquals('4.581550', $c->add('3.1233404', '1.45821'));
        $this->assertEquals('4.581551', $c->plus(3.1233408, 1.45821));
        $this->assertEquals(3, $c->add(1, 2));
        $this->assertEquals(3, $c->plus('54643590809854390584309', '-54643590809854390584306'));
        $this->assertEquals(3.00009, $c->add('54643590809854390584309.000098', '-54643590809854390584306.000008'));
        $this->assertEquals(3.0009, $c->plus('54643590809854390584309.00098', '-54643590809854390584306.00008'));

        $this->assertEquals('1.66523', $c->sub('3.12344', '1.45821'));
        $this->assertEquals(1.665235, $c->minus(3.12344543, 1.45821));
        $this->assertEquals('1.66423', $c->sub('3.12304', '1.45881'));
        $this->assertEquals(1.664274, $c->minus(3.123084, 1.45881));
        $this->assertEquals(-1, $c->sub(1, 2));
        $this->assertEquals(3, $c->minus('54643590809854390584309', '54643590809854390584306'));
        $this->assertEquals(3.00009, $c->sub('54643590809854390584309.000098', '54643590809854390584306.000008'));
        $this->assertEquals(3.000001, $c->minus('54643590809854390584309.00000098', '54643590809854390584306.00000008'));

        $this->assertEquals('4.554350', $c->multiply('3.12344', '1.45812'));
        $this->assertEquals('4.554358', $c->times(3.12344543, 1.45812));
        $this->assertEquals('4.555922', $c->multiply('3.12304', '1.45881'));
        $this->assertEquals('4.556111', $c->times(3.1234304, 1.4586881));  //4.5561107557
        $this->assertEquals(2, $c->multiply(1, 2));
        $this->assertEquals('2985922016594803213441652876530088311265254554', $c->times('54643590809854390584309', '54643590809854390584306'));
        $this->assertEquals(183506098.67506, $c->multiply('30584309.000098', '6.000008'));
        $this->assertEquals(15476274486.97431, $c->times('30584309.000098', '506.02008'));

        $this->assertEquals('2.142101', $c->divideby('3.12344', '1.45812'));
        $this->assertEquals('2.142105', $c->div(3.12344543, 1.45812));
        $this->assertEquals('2.140801', $c->divideby('3.12300004', '1.45880001'));
        $this->assertEquals('2.140813', $c->div(3.12304, 1.45881));
        $this->assertEquals(0.5, $c->divideby(1, 2));
        $this->assertEquals('1', $c->div('54643590809854390584309', '54643590809854390584306'));
        $this->assertEquals(5097378.0368460, $c->divideby('30584309.000098', '6.000008'));
    }
}