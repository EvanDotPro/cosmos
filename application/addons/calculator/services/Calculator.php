<?php
/**
 * Calculator - sample class to expose via JSON-RPC
 */
class Calculator
{
	/**
	 * Return sum of two variables
	 *
	 * @param  int $x
	 * @param  int $y
	 * @return int
	 */
	public function add($x, $y)
	{
		return $x + $y;
	}

	/**
	 * Return difference of two variables
	 *
	 * @param  int $x
	 * @param  int $y
	 * @return int
	 */
	public function subtract($x, $y)
	{
		return $x - $y;
	}

	/**
	 * Return product of two variables
	 *
	 * @param  int $x
	 * @param  int $y
	 * @return int
	 */
	public function multiply($x, $y)
	{
		return $x * $y;
	}

	/**
	 * Return the quotient of two variables
	 *
	 * @param  int $x
	 * @param  int $y
	 * @return float
	 */
	public function divide($x, $y)
	{
		return $x / $y;
	}

	/**
	 * Return the remainder of two variables' quotient
	 *
	 * @param  int $x
	 * @param  int $y
	 * @return float
	 */
	public function modulus($x, $y)
	{
		return $x % $y;
	}
}