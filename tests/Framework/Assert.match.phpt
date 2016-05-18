<?php

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$matches = [
	['1', '1'],
	['1', 1],
	['a', "a  \t\r\n\t \n"],
	["a \t\r\n", 'a'],
	['%%', '%'],
	['%%a%%', '%a%'],
	['%a%', 'a b'],
	['%a?%', 'a b'],
	['%a?%', ''],
	['%A%', "a\nb"],
	['%A?%', "a\nb"],
	['%A?%', ''],
	['%s%', " \t"],
	['%s?%', " \t"],
	['%s?%', ''],
	['a%c%c', 'abc'],
	['a%c%c', 'a c'],
	['%d%', '123'],
	['%d?%', '123'],
	['%d?%', ''],
	['%i%', '-123'],
	['%i%', '+123'],
	['%f%', '-123'],
	['%f%', '+123.5'],
	['%f%', '-1e5'],
	['%h%', 'aBcDeF01'],
	['%w%', 'aBzZ_01'],
	['%ds%%ds%', '\\/'],
	['%[a-c]+%', 'abc'],
	['%[]%', '%[]%'],
	['.\\+*?[^]$(){}=!<>|:-#', '.\\+*?[^]$(){}=!<>|:-#'],
];

$notMatches = [
	['', 'a', ''],
	['a', ' a ', 'a'],
	['%a%', "a\nb", 'a'],
	['%a%', '', '%a%'],
	['%A%', '', '%A%'],
	['a%s%b', "a\nb", 'a%s%b',],
	['%s?%', 'a', ''],
	['a%c%c', 'abbc', 'abc'],
	['a%c%c', 'ac', 'acc'],
	['a%c%c', "a\nc", 'a%c%c'],
	['%d%', '', '%d%'],
	['%i%', '-123.5', '-123'],
	['%i%', '', '%i%'],
	['%f%', '', '%f%'],
	['%h%', 'gh', '%h%'],
	['%h%', '', '%h%'],
	['%w%', ',', '%w%'],
	['%w%', '', '%w%'],
	['%[a-c]+%', 'Abc', '%[a-c]+%'],
	['foo%d%foo', 'foo123baz', 'foo123foo'],
	['foo%d%bar', 'foo123baz', 'foo123bar'],
	['foo%d?%foo', 'foo123baz', 'foo123foo'],
	['foo%d?%bar', 'foo123baz', 'foo123bar'],
	['%a%x', 'abc', 'abcx'],
];

foreach ($matches as $case) {
	list($expected, $value) = $case;
	Assert::match($expected, $value);
}

foreach ($notMatches as $case) {
	list($expected, $value, $pattern) = $case;
	$pattern = str_replace('%', '%%', $pattern);
	$value = str_replace('%', '%%', $value);

	Assert::exception(function () use ($expected, $value) {
		Assert::match($expected, $value);
	}, 'Tester\AssertException', "'$value' should match '$pattern'");
}


Assert::same('', Assert::expandMatchingPatterns('', ''));
Assert::same('abc', Assert::expandMatchingPatterns('abc', 'a'));
Assert::same('a', Assert::expandMatchingPatterns('%a?%', 'a'));
Assert::same('123a', Assert::expandMatchingPatterns('%d?%a', '123b'));
Assert::same('a', Assert::expandMatchingPatterns('a', 'a'));
Assert::same('ab', Assert::expandMatchingPatterns('ab', 'abc'));
Assert::same('abcx', Assert::expandMatchingPatterns('%a%x', 'abc'));
Assert::same('a123c', Assert::expandMatchingPatterns('a%d%c', 'a123x'));
Assert::same('a%A%b', Assert::expandMatchingPatterns('a%A%b', 'axc'));


Assert::exception(function () {
	Assert::match(NULL, '');
}, 'Exception', 'Pattern must be a string.');


Assert::matchFile(__DIR__ . '/Assert.matchFile.txt', '! Hello !');

Assert::exception(function () {
	Assert::match('a', 'b', 'Custom description');
}, 'Tester\AssertException', 'Custom description: %A% should match %A%');

Assert::exception(function () {
	Assert::matchFile(__DIR__ . '/Assert.matchFile.txt', '! Not match !', 'Custom description');
}, 'Tester\AssertException', 'Custom description: %A% should match %A%');
