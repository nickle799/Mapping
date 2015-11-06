<?php
namespace NickLewis\Mapping\Services;
use Bullhorn\FastRest\DependencyInjection;
use Phalcon\Di\InjectionAwareInterface;

abstract class Root implements InjectionAwareInterface {
	use DependencyInjection;

}