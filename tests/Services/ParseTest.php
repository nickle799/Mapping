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
	 * testParse_closingQuoteNotAtEnd
	 * @return void
	 * @expectedException \NickLewis\Mapping\Services\CatchableException
	 * @expectedExceptionMessage Invalid Mapping: A closing quote "\"" must either be followed by the end of the mapping, a closing parenthesis, or a plus
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

}