<?php
namespace Tests\Services\Parse;
use NickLewis\Mapping\Models\ObjectInterface;
use NickLewis\Mapping\Services\Parse;
use PHPUnit_Framework_MockObject_MockObject;
use Tests\Root;

class ParseTest extends Root {

	/**
	 * runMapping
	 * @param string $mapping
	 * @return mixed
	 */
	private function runMapping($mapping) {
		/** @type ObjectInterface|PHPUnit_Framework_MockObject_MockObject $currentObject */
		$currentObject = $this->getMockBuilder(ObjectInterface::class)->getMock();
		$currentObject->expects($this->any())
			->method('getMappableFields')
			->willReturn([]);

		$parse = new Parse($currentObject);
		return $parse->parse($mapping);
	}

	/**
	 * testDate
	 * @return void
	 */
	public function testDate() {
		$actual = $this->runMapping('"2015-05-06".date("m/d/Y")');
		$this->assertEquals('05/06/2015', $actual);
	}

	/**
	 * testParse_escapeAtEnd
	 * @return void
	 * @expectedException \NickLewis\Mapping\Services\CatchableException
	 * @expectedExceptionMessage Invalid Mapping: An escape character "\" must either be escaped or not at the end of a mapping
	 */
	public function testParse_escapeAtEnd() {
		$this->runMapping('\\');
	}

	/**
	 * testParse_validEscape
	 * @return void
	 */
	public function testParse_validEscape() {
		$actual = $this->runMapping('"hello\"goodbye"');
		$this->assertEquals('hello"goodbye', $actual);
	}

	/**
	 * testParse_closingQuoteNotAtEnd
	 * @return void
	 * @expectedException \NickLewis\Mapping\Services\CatchableException
	 * @expectedExceptionMessage Invalid Mapping: A closing quote "\"" must either be followed by the end of the mapping, a closing parenthesis, a plus, a "," or a .
	 */
	public function testParse_closingQuoteNotAtEnd() {
		$this->runMapping('""d');
	}

	/**
	 * testParse_openingQuoteNotAtBeginning
	 * @return void
	 * @expectedException \NickLewis\Mapping\Services\CatchableException
	 * @expectedExceptionMessage Invalid Mapping: A beginning quote "\"" must be at the beginning of a mapping
	 */
	public function testParse_openingQuoteNotAtBeginning() {
		$this->runMapping('d"');
	}

	/**
	 * testParse_noClosingQuote
	 * @return void
	 * @expectedException \NickLewis\Mapping\Services\CatchableException
	 * @expectedExceptionMessage Invalid Mapping: There is no matching closing quote to an opening quote
	 */
	public function testParse_noClosingQuote() {
		$this->runMapping('"');
	}

	/**
	 * testParse_moreClosingThanOpeningParenthesis
	 * @return void
	 * @expectedException \NickLewis\Mapping\Services\CatchableException
	 * @expectedExceptionMessage Invalid Mapping: There are more opening parenthesis than closing parenthesis
	 */
	public function testParse_moreOpeningThanClosingParenthesis() {
		$this->runMapping('(adf');
	}

	/**
	 * testParse_moreOpeningThanClosingParenthesisOnlyOpenParenthesis
	 * @return void
	 * @expectedException \NickLewis\Mapping\Services\CatchableException
	 * @expectedExceptionMessage Invalid Mapping: There are more opening parenthesis than closing parenthesis
	 */
	public function testParse_moreOpeningThanClosingParenthesisOnlyOpenParenthesis() {
		$this->runMapping('(');
	}

	/**
	 * testParse_moreClosingThanOpeningParenthesis
	 * @return void
	 * @expectedException \NickLewis\Mapping\Services\CatchableException
	 * @expectedExceptionMessage Invalid Mapping: There are more closing parenthesis than opening parenthesis
	 */
	public function testParse_moreClosingThanOpeningParenthesis() {
		$this->runMapping(')');
	}

	/**
	 * testParse_moreClosingThanOpeningParenthesis
	 * @return void
	 */
	public function testParse_validStringOnly() {
		$actual = $this->runMapping('"hello"');
		$this->assertEquals('hello', $actual);
	}

	/**
	 * testParse_twoConcatenatedStrings
	 * @return void
	 */
	public function testParse_twoConcatenatedStrings() {
		$actual = $this->runMapping('"hello"+"goodbye"');
		$this->assertEquals('hellogoodbye', $actual);
	}

	/**
	 * testParse_arbitraryComma
	 * @expectedException \NickLewis\Mapping\Services\CatchableException
	 * @expectedExceptionMessage Invalid Mapping: Commas are only allowed inside of parameters for methods
	 * @return void
	 */
	public function testParse_arbitraryComma() {
		$this->runMapping(',');
	}

	/**
	 * testSubString_noParameters
	 * @return void
	 * @expectedException \NickLewis\Mapping\Services\CatchableException
	 * @expectedExceptionMessage Missing Required Parameter (The offset from the beginning of the string.  If it is negative, it will be offset from the end of the string)
	 */
	public function testSubString_noParameters() {
		$this->runMapping('"hello".substring()');
	}

	/**
	 * testSubString_noParameters
	 * @return void
	 */
	public function testSubString_onlyStart() {
		$actual = $this->runMapping('"hello".substring("2")');
		$this->assertEquals('llo', $actual);
	}

	/**
	 * testSubString_noParameters
	 * @return void
	 */
	public function testSubString_startAndLength() {
		$actual = $this->runMapping('"hello".substring("2","2")');
		$this->assertEquals('ll', $actual);
	}

	/**
	 * testSubString_noParameters
	 * @return void
	 * @expectedException \NickLewis\Mapping\Services\CatchableException
	 * @expectedExceptionMessage Missing Required Parameter (The parameter to add to)
	 */
	public function testAdd_noParameters() {
		$this->runMapping('"1.5".add()');
	}

	/**
	 * testSubString_noParameters
	 * @return void
	 */
	public function testAdd_valid() {
		$actual = $this->runMapping('"1.5".add("2.7")');
		$this->assertEquals(4.2, $actual->getValue());
	}

	/**
	 * testSubString_noParameters
	 * @return void
	 * @expectedException \NickLewis\Mapping\Services\CatchableException
	 * @expectedExceptionMessage Missing Required Parameter (The parameter to subtract)
	 */
	public function testSubtract_noParameters() {
		$this->runMapping('"1.5".subtract()');
	}

	/**
	 * testSubString_noParameters
	 * @return void
	 */
	public function testSubtract_valid() {
		$actual = $this->runMapping('"1.5".subtract("2.7")');
		$this->assertEquals(-1.2, $actual->getValue());
	}

	/**
	 * testSubString_noParameters
	 * @return void
	 * @expectedException \NickLewis\Mapping\Services\CatchableException
	 * @expectedExceptionMessage Missing Required Parameter (The parameter to multiply)
	 */
	public function testMultiply_noParameters() {
		$this->runMapping('"1.5".multiply()');
	}

	/**
	 * testSubString_noParameters
	 * @return void
	 */
	public function testMultiply_valid() {
		$actual = $this->runMapping('"1.5".multiply("2.7")');
		$this->assertEquals(4.05, $actual->getValue());
	}

	/**
	 * testSubString_noParameters
	 * @return void
	 * @expectedException \NickLewis\Mapping\Services\CatchableException
	 * @expectedExceptionMessage Missing Required Parameter (The parameter to divide)
	 */
	public function testDivide_noParameters() {
		$this->runMapping('"1.5".divide()');
	}

	/**
	 * testSubString_noParameters
	 * @return void
	 */
	public function testDivide_valid() {
		$actual = $this->runMapping('"1.5".divide("2.7")');
		$this->assertEquals(0.555556, $actual->getValue());
	}

	/**
	 * testSubString_noParameters
	 * @return void
	 */
	public function testRound_noParameters() {
		$actual = $this->runMapping('"1.5".round()');
		$this->assertEquals(2, $actual->getValue());
	}

	/**
	 * testRound_withParameter
	 * @return void
	 */
	public function testRound_withParameter() {
		$actual = $this->runMapping('"1.23152".round("2")');
		$this->assertEquals(1.23, $actual->getValue());
	}

	/**
	 * testIn_threeParamsNoMapping
	 * @return void
	 */
	public function testIn_threeParamsNoMapping() {
		$actual = $this->runMapping('"hello".in("abc","abd","abe")');
		$this->assertSame(false, $actual->getValue());
	}

	/**
	 * testIn_valid
	 * @return void
	 */
	public function testIn_valid() {
		$actual = $this->runMapping('"hello".in("abc","abd","abe","hello")');
		$this->assertSame(true, $actual->getValue());
	}

	/**
	 * testNot_true
	 * @return void
	 */
	public function testNot_true() {
		$actual = $this->runMapping('"hello".in("hello").not()');
		$this->assertSame(false, $actual->getValue());
	}

	/**
	 * testNot_false
	 * @return void
	 */
	public function testNot_false() {
		$actual = $this->runMapping('"hello".in("hello").not()');
		$this->assertSame(true, $actual->getValue());
	}

	/**
	 * testIn_valid
	 * @return void
	 */
	public function testIfThen_true() {
		$actual = $this->runMapping('"hello".in("hello").ifThen("itIsTrue")');
		$this->assertSame("itIsTrue", $actual->__toString());
	}

	/**
	 * testIn_valid
	 * @return void
	 */
	public function testIfThen_false() {
		$actual = $this->runMapping('"hello".in("helloWorld").ifThen("itIsTrue","itIsFalse")');
		$this->assertSame("itIsFalse", $actual->__toString());
	}

	/**
	 * testToLowerCase
	 * @return void
	 */
	public function testToLowerCase() {
		$actual = $this->runMapping('"HeLlo".toLowerCase()');
		$this->assertSame("hello", $actual->__toString());
	}

	/**
	 * testToUpperCase
	 * @return void
	 */
	public function testToUpperCase() {
		$actual = $this->runMapping('"HeLlo".toUpperCase()');
		$this->assertSame("HELLO", $actual->__toString());
	}

	/**
	 * testTrim_noParams
	 * @return void
	 */
	public function testTrim_noParams() {
		$actual = $this->runMapping('" hello
		 ".trim()');
		$this->assertSame("hello", $actual->__toString());
	}

	/**
	 * testTrim_noParams
	 * @return void
	 */
	public function testTrim_parameter() {
		$actual = $this->runMapping('"hello".trim("ho")');
		$this->assertSame("ell", $actual->__toString());
	}

	/**
	 * testLeftFill_noParameters
	 * @return void
	 * @expectedException \NickLewis\Mapping\Services\CatchableException
	 * @expectedExceptionMessage Missing Required Parameter (The length to fill the string to)
	 */
	public function testLeftFill_noParameters() {
		$this->runMapping('"hello".leftFill()');
	}

	/**
	 * testLeftFill_noParameters
	 * @return void
	 */
	public function testLeftFill_onlyLength() {
		$actual = $this->runMapping('"hello".leftFill("10")');
		$this->assertSame("00000hello", $actual->__toString());
	}

	/**
	 * testLeftFill_noParameters
	 * @return void
	 * @expectedException \NickLewis\Mapping\Services\CatchableException
	 * @expectedExceptionMessage Invalid Mapping: Fill String Cannot be empty looking at: leftFill (Offset: 25) with full mapping "hello".leftFill("10","")
	 */
	public function testLeftFill_emptyFillString() {
		$this->runMapping('"hello".leftFill("10","")');
	}

	/**
	 * testLeftFill_noParameters
	 * @return void
	 */
	public function testLeftFill_validFillString() {
		$actual = $this->runMapping('"hello".leftFill("10","ab")');
		$this->assertSame("ababahello", $actual->__toString());
	}

	/**
	 * testRightFill_noParameters
	 * @return void
	 * @expectedException \NickLewis\Mapping\Services\CatchableException
	 * @expectedExceptionMessage Missing Required Parameter (The length to fill the string to)
	 */
	public function testRightFill_noParameters() {
		$this->runMapping('"hello".rightFill()');
	}

	/**
	 * testRightFill_noParameters
	 * @return void
	 */
	public function testRightFill_onlyLength() {
		$actual = $this->runMapping('"hello".rightFill("10")');
		$this->assertSame("hello     ", $actual->__toString());
	}

	/**
	 * testRightFill_noParameters
	 * @return void
	 * @expectedException \NickLewis\Mapping\Services\CatchableException
	 * @expectedExceptionMessage Invalid Mapping: Fill String Cannot be empty looking at: rightFill (Offset: 26) with full mapping "hello".rightFill("10","")
	 */
	public function testRightFill_emptyFillString() {
		$this->runMapping('"hello".rightFill("10","")');
	}

	/**
	 * testRightFill_noParameters
	 * @return void
	 */
	public function testRightFill_validFillString() {
		$actual = $this->runMapping('"hello".rightFill("10","ab")');
		$this->assertSame("helloababa", $actual->__toString());
	}

	/**
	 * testRightFill_noParameters
	 * @return void
	 * @expectedException \NickLewis\Mapping\Services\CatchableException
	 * @expectedExceptionMessage Invalid Mapping: Missing Required Parameter (The parameter to check against)
	 */
	public function testGreaterThan_noParameters() {
		$this->runMapping('"5".greaterThan()');
	}

	/**
	 * testGreaterThan_true
	 * @return void
	 */
	public function testGreaterThan_true() {
		$actual = $this->runMapping('"5.3".greaterThan("-3.5")');
		$this->assertSame(true, $actual->getValue());
	}

	/**
	 * testGreaterThan_false
	 * @return void
	 */
	public function testGreaterThan_false() {
		$actual = $this->runMapping('"-5.3".greaterThan("-3.5")');
		$this->assertSame(false, $actual->getValue());
	}

	/**
	 * testRightFill_noParameters
	 * @return void
	 * @expectedException \NickLewis\Mapping\Services\CatchableException
	 * @expectedExceptionMessage Invalid Mapping: Missing Required Parameter (The parameter to check against)
	 */
	public function testLessThan_noParameters() {
		$this->runMapping('"5".lessThan()');
	}

	/**
	 * testLessThan_true
	 * @return void
	 */
	public function testLessThan_true() {
		$actual = $this->runMapping('"-5.3".lessThan("-3.5")');
		$this->assertSame(true, $actual->getValue());
	}

	/**
	 * testLessThan_false
	 * @return void
	 */
	public function testLessThan_false() {
		$actual = $this->runMapping('"5.3".lessThan("-3.5")');
		$this->assertSame(false, $actual->getValue());
	}

	/**
	 * testMap_exactMatch
	 * @return void
	 */
	public function testMap_exactMatch() {
		$actual = $this->runMapping('"hello world".map("*","sweet","hello world","goodbye")');
		$this->assertEquals('goodbye', $actual->__toString());
	}

	/**
	 * testMap_noMatch
	 * @return void
	 */
	public function testMap_noMatch() {
		$actual = $this->runMapping('"hello world".map("help world","goodbye")');
		$this->assertEquals('hello world', $actual->__toString());
	}

	/**
	 * testMap_noMatch
	 * @return void
	 */
	public function testMap_wildCard() {
		$actual = $this->runMapping('"hello world".map("*","sweet","help world","goodbye")');
		$this->assertEquals('sweet', $actual->__toString());
	}

	/**
	 * testMap_noMatch
	 * @return void
	 */
	public function testMap_wildPartOfString() {
		$actual = $this->runMapping('"hello world".map("*","sweet","*el* wor*","goodbye")');
		$this->assertEquals('goodbye', $actual->__toString());
	}

}