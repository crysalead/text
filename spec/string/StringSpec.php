<?php
namespace string\spec;

use stdClass;
use Exception;
use string\String;
use kahlan\plugin\Stub;
use InvalidArgumentException;

describe("String", function() {

    describe("insert", function() {

        it("inserts scalar variables in a string", function() {

            $string = 'Obi-Wan is {:adjective}.';
            $expected = 'Obi-Wan is awesome.';
            $result = String::insert($string, ['adjective' => 'awesome']);
            $this->expect($result)->toBe($expected);

        });

        it("inserts object variables supporting `__toString()` in a string", function() {

            $string = 'Obi-Wan is a {:noun}.';
            $expected = 'Obi-Wan is a jedi.';

            $stub = Stub::create();
            Stub::on($stub)->method('__toString')->andReturn('jedi');

            $result = String::insert($string, ['noun' => $stub]);
            $this->expect($result)->toBe($expected);

        });

        it("inserts a blank for object variables which doesn't support `__toString()`", function() {

            $string = 'Obi-Wan is a {:noun}.';
            $expected = 'Obi-Wan is a .';

            $result = String::insert($string, ['noun' => new stdClass()]);
            $this->expect($result)->toBe($expected);

        });

        it("inserts a variable as many time as it exists a placeholder", function() {

            $string = '{:a} {:b} {:a} {:a}';
            $expected = '1 2 1 1';
            $result = String::insert($string, ['a' => 1, 'b' => 2]);
            $this->expect($result)->toBe($expected);

        });

        it("inserts a variable with custom placeholder", function() {

            $string = '%a %b %a %a';
            $expected = '1 2 1 1';
            $result = String::insert($string, ['a' => 1, 'b' => 2], ['before' => '%', 'after' => '']);
            $this->expect($result)->toBe($expected);

        });

        it("escapes escaped placeholder", function() {

            $string = '{:a} {:b} \{:a} {:a}';
            $expected = '1 2 {:a} 1';
            $result = String::insert($string, ['a' => 1, 'b' => 2], ['escape' => '\\']);
            $this->expect($result)->toBe($expected);

        });

    });

});

describe("String", function() {

    describe("clean", function() {

        it("cleans placeholder", function() {

            $result = String::clean('{:incomplete}');
            $this->expect($result)->toBe('');

        });

        it("cleans placeholder with a default string", function() {

            $result = String::clean('{:incomplete}', ['replacement' => 'complete']);
            $this->expect($result)->toBe('complete');

        });

        it("cleans placeholder and adjacent spaces", function() {

            $result = String::clean('{:a} 2 3');
            $this->expect($result)->toBe('2 3');

            $result = String::clean('2 {:a} 3');
            $this->expect($result)->toBe('2 3');

            $result = String::clean('2 3 {:a}');
            $this->expect($result)->toBe('2 3');

        });

        it("cleans placeholder and adjacent commas", function() {

            $result = String::clean('{:a}, 2, 3');
            $this->expect($result)->toBe('2, 3');

            $result = String::clean('2, {:a}, 3');
            $this->expect($result)->toBe('2, 3');

            $result = String::clean('{:a}, {:b}, 3');
            $this->expect($result)->toBe('3');

            $result = String::clean('{:a}, 3, {:b}');
            $this->expect($result)->toBe('3');

            $result = String::clean('{:a}, {:b}, {:c}');
            $this->expect($result)->toBe('');

        });

        it("cleans placeholder and adjacent `'and'`", function() {

            $result = String::clean('{:a} and 2 and 3');
            $this->expect($result)->toBe('2 and 3');

            $result = String::clean('2 and {:a} and 3');
            $this->expect($result)->toBe('2 and 3');

            $result = String::clean('{:a} and {:b} and 3');
            $this->expect($result)->toBe('3');

            $result = String::clean('{:a} and 3 and {:b}');
            $this->expect($result)->toBe('3');

            $result = String::clean('{:a} and {:b} and {:c}');
            $this->expect($result)->toBe('');

        });

        it("cleans placeholder and adjacent comma and `'and'`", function() {

            $result = String::clean('{:a}, 2 and 3');
            $this->expect($result)->toBe('2 and 3');

            $result = String::clean('{:a}, 2 and {:c}');
            $this->expect($result)->toBe('2');

        });

    });

});

describe("String", function() {

    describe("toString", function() {

        it("exports an empty array", function() {

            $dump = String::toString([]);
            $this->expect($dump)->toBe("[]");

        });

        it("exports an object", function() {

            $dump = String::toString(new stdClass());
            $this->expect($dump)->toBe("`stdClass`");

        });

        it("exports an object supporting __toString()", function() {

            $stub = Stub::create();
            Stub::on($stub)->method('__toString')->andReturn('jedi');

            $dump = String::toString($stub);
            $this->expect($dump)->toBe("jedi");

        });

        it("exports an object using a closure", function() {

            $toString = function($instance) {
                return 'an instance of `' . get_class($instance) . '`';
            };
            $dump = String::toString(new stdClass(), ['object' => ['method' => $toString]]);
            $this->expect($dump)->toBe("an instance of `stdClass`");

        });

        it("exports an exception", function() {

            $dump = String::toString(new Exception());
            $this->expect($dump)->toMatch("/`Exception` Code\(0\) with no message in .*?\/StringSpec.php.*?$/");

            $dump = String::toString(new Exception('error', 500));
            $this->expect($dump)->toMatch("/`Exception` Code\(500\) with message \"error\" in .*?\/StringSpec.php.*?$/");

        });

        it("exports a Closure", function() {

            $dump = String::toString(function(){});
            $this->expect($dump)->toBe("`Closure`");

        });

        context("with double quote", function() {

            it("exports a string", function() {

                $dump = String::toString('Hello', ['quote' => '"']);
                $this->expect($dump)->toBe('"Hello"');

            });

            it("escapes double quote", function() {

                $dump = String::toString('Hel"lo', ['quote' => '"']);
                $this->expect($dump)->toBe('"Hel\"lo"');

            });

            it("doesn't escape simple quote", function() {

                $dump = String::toString("Hel'lo", ['quote' => '"']);
                $this->expect($dump)->toBe('"Hel\'lo"');

            });

            it("exports an array", function() {

                $dump = String::toString(['Hello', 'World'], ['quote' => '"']);
                $this->expect($dump)->toBe("[\n    0 => \"Hello\",\n    1 => \"World\"\n]");

            });

            it("exports an nested array", function() {

                $dump = String::toString([['Hello'], ['World']], ['quote' => '"']);
                $this->expect($dump)->toBe("[\n    0 => [\n        0 => \"Hello\"\n    ],\n    1 => [\n        0 => \"World\"\n    ]\n]");

            });

            it("exports an array using string as key", function() {

                $dump = String::toString(['Hello' => 'World'], ['quote' => '"']);
                $this->expect($dump)->toBe("[\n    \"Hello\" => \"World\"\n]");

            });

        });

        context("with simple quote", function() {

            it("exports a string", function() {

                $dump = String::toString('Hello', ['quote' => "'"]);
                $this->expect($dump)->toBe("'Hello'");

            });

            it("escapes simple quote", function() {

                $dump = String::toString("Hel'lo", ['quote' => "'"]);
                $this->expect($dump)->toBe("'Hel\\'lo'");

            });

            it("doesn't escape double quote", function() {

                $dump = String::toString('Hel"lo', ['quote' => "'"]);
                $this->expect($dump)->toBe("'Hel\"lo'");

            });

            it("exports an array", function() {

                $dump = String::toString(['Hello', 'World'], ['quote' => "'"]);
                $this->expect($dump)->toBe("[\n    0 => 'Hello',\n    1 => 'World'\n]");

            });

            it("exports an nested array", function() {

                $dump = String::toString([['Hello'], ['World']], ['quote' => "'"]);
                $this->expect($dump)->toBe("[\n    0 => [\n        0 => 'Hello'\n    ],\n    1 => [\n        0 => 'World'\n    ]\n]");

            });

            it("exports an array using string as key", function() {

                $dump = String::toString(['Hello' => 'World'], ['quote' => "'"]);
                $this->expect($dump)->toBe("[\n    'Hello' => 'World'\n]");

            });

        });

        context("with no quote", function() {

            it("exports a string to a non quoted string dump", function() {

                $dump = String::toString('Hello', ['quote' => false]);
                $this->expect($dump)->toBe('Hello');

            });

        });

    });

});

describe("String", function() {

    describe("dump", function() {

        it("dumps null to a string dump", function() {

            $dump = String::dump(null);
            $this->expect($dump)->toBe("null");

        });

        it("dumps booleans to a string dump", function() {

            $dump = String::dump(true);
            $this->expect($dump)->toBe("true");

            $dump = String::dump(false);
            $this->expect($dump)->toBe("false");

        });

        it("dumps numeric to a string dump", function() {

            $dump = String::dump(77);
            $this->expect($dump)->toBe("77");

            $dump = String::dump(3.141592);
            $this->expect($dump)->toBe("3.141592");

        });

        it("dumps a string with double quote", function() {

            $dump = String::dump('Hel"lo');
            $this->expect($dump)->toBe('"Hel\\"lo"');

        });

        it("dumps a string with simple quote", function() {

            $dump = String::dump("Hel'lo", "'");
            $this->expect($dump)->toBe("'Hel\'lo'");

        });

        it("expands escape sequences and escape special chars", function() {

            $dump = String::dump(" \t \nHello \x07 \x08 \r\n \v \f World\n\n");
            $this->expect($dump)->toBe("\" \\t \\nHello \\x07 \\x08 \\r\\n \\v \\f World\\n\\n\"");

        });

        it("expands an empty string as \"\"", function() {

            $dump = String::dump('');
            $this->expect($dump)->toBe('""');

        });

        it("expands an zero string as 0", function() {

            $dump = String::dump('2014');
            $this->expect($dump)->toBe('"2014"');

        });

        it("expands espcape special chars", function() {

            $dump = String::dump('20$14');
            $this->expect($dump)->toBe('"20\$14"');

            $dump = String::dump('20"14');
            $this->expect($dump)->toBe('"20\"14"');

            $dump = String::dump('20\14');
            $this->expect($dump)->toBe('"20\\\14"');

        });

    });

});
