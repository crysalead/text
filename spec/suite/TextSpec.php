<?php
namespace text\spec\suite;

use stdClass;
use Exception;
use text\Text;
use kahlan\plugin\Stub;
use InvalidArgumentException;

describe("Text", function() {

    describe("::insert()", function() {

        it("inserts scalar variables in a string", function() {

            $string = 'Obi-Wan is {:adjective}.';
            $expected = 'Obi-Wan is awesome.';
            $result = Text::insert($string, ['adjective' => 'awesome']);
            expect($result)->toBe($expected);

        });

        it("inserts object variables supporting `__toString()` in a string", function() {

            $string = 'Obi-Wan is a {:noun}.';
            $expected = 'Obi-Wan is a jedi.';

            $stub = Stub::create();
            Stub::on($stub)->method('__toString')->andReturn('jedi');

            $result = Text::insert($string, ['noun' => $stub]);
            expect($result)->toBe($expected);

        });

        it("inserts a blank for object variables which doesn't support `__toString()`", function() {

            $string = 'Obi-Wan is a {:noun}.';
            $expected = 'Obi-Wan is a .';

            $result = Text::insert($string, ['noun' => new stdClass()]);
            expect($result)->toBe($expected);

        });

        it("inserts a variable as many time as it exists a placeholder", function() {

            $string = '{:a} {:b} {:a} {:a}';
            $expected = '1 2 1 1';
            $result = Text::insert($string, ['a' => 1, 'b' => 2]);
            expect($result)->toBe($expected);

        });

        it("inserts a variable with custom placeholder", function() {

            $string = '%a %b %a %a';
            $expected = '1 2 1 1';
            $result = Text::insert($string, ['a' => 1, 'b' => 2], ['before' => '%', 'after' => '']);
            expect($result)->toBe($expected);

        });

        it("escapes escaped placeholder", function() {

            $string = '{:a} {:b} \{:a} {:a}';
            $expected = '1 2 {:a} 1';
            $result = Text::insert($string, ['a' => 1, 'b' => 2], ['escape' => '\\']);
            expect($result)->toBe($expected);

        });

    });

    describe("::clean()", function() {

        it("cleans placeholder", function() {

            $result = Text::clean('{:incomplete}');
            expect($result)->toBe('');

        });

        it("cleans placeholder with a default string", function() {

            $result = Text::clean('{:incomplete}', ['replacement' => 'complete']);
            expect($result)->toBe('complete');

        });

        it("cleans placeholder and adjacent spaces", function() {

            $result = Text::clean('{:a} 2 3');
            expect($result)->toBe('2 3');

            $result = Text::clean('2 {:a} 3');
            expect($result)->toBe('2 3');

            $result = Text::clean('2 3 {:a}');
            expect($result)->toBe('2 3');

        });

        it("cleans placeholder and adjacent commas", function() {

            $result = Text::clean('{:a}, 2, 3');
            expect($result)->toBe('2, 3');

            $result = Text::clean('2, {:a}, 3');
            expect($result)->toBe('2, 3');

            $result = Text::clean('{:a}, {:b}, 3');
            expect($result)->toBe('3');

            $result = Text::clean('{:a}, 3, {:b}');
            expect($result)->toBe('3');

            $result = Text::clean('{:a}, {:b}, {:c}');
            expect($result)->toBe('');

        });

        it("cleans placeholder and adjacent `'and'`", function() {

            $result = Text::clean('{:a} and 2 and 3');
            expect($result)->toBe('2 and 3');

            $result = Text::clean('2 and {:a} and 3');
            expect($result)->toBe('2 and 3');

            $result = Text::clean('{:a} and {:b} and 3');
            expect($result)->toBe('3');

            $result = Text::clean('{:a} and 3 and {:b}');
            expect($result)->toBe('3');

            $result = Text::clean('{:a} and {:b} and {:c}');
            expect($result)->toBe('');

        });

        it("cleans placeholder and adjacent comma and `'and'`", function() {

            $result = Text::clean('{:a}, 2 and 3');
            expect($result)->toBe('2 and 3');

            $result = Text::clean('{:a}, 2 and {:c}');
            expect($result)->toBe('2');

        });

        it("cleans placeholder with special chars", function() {

            $string = '${a} ${b}';
            $expected = '';
            $result = Text::clean($string, ['before' => '${', 'after' => '}']);
            expect($result)->toBe($expected);

        });

    });

});
