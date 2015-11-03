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
		$this->assertEquals($actual, 'llo');
	}

	/**
	 * testSubString_noParameters
	 * @return void
	 */
	public function testSubString_startAndLength() {
		$actual = $this->runMapping('"hello".substring("2","2")');
		$this->assertEquals($actual, 'll');
	}

	/**
	 * testSubString_noParameters
	 * @return void
	 * @expectedException \NickLewis\Mapping\Services\CatchableException
	 * @expectedExceptionMessage Missing Required Parameter (The parameter to add to)
	 */
	public function testAdd_noParameters() {
		$actual = $this->runMapping('"1.5".add()');
		$this->assertEquals($actual, '4.2');
	}

	/**
	 * testSubString_noParameters
	 * @return void
	 */
	public function testAdd_valid() {
		$actual = $this->runMapping('"1.5".add("2.7")');
		$this->assertEquals($actual, '4.2');
	}

}