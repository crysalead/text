<?php
namespace text;

use Closure;
use Exception;
use InvalidArgumentException;

class Text
{
    /**
     * Replaces variable placeholders inside a string with any given data. Each key
     * in the `$data` array corresponds to a variable placeholder name in `$str`.
     *
     * Usage:
     * {{{
     * Text::insert(
     *     'My name is {:name} and I am {:age} years old.', ['name' => 'Bob', 'age' => '65']
     * );
     * }}}
     *
     * @param  string $str     A string containing variable place-holders.
     * @param  array  $data    A key, value array where each key stands for a place-holder variable
     *                         name to be replaced with value.
     * @param  array  $options Available options are:
     *                         - `'before'`: The character or string in front of the name of the variable
     *                           place-holder (defaults to `'{:'`).
     *                         - `'after'`: The character or string after the name of the variable
     *                           place-holder (defaults to `}`).
     *                         - `'escape'`: The character or string used to escape the before character or string
     *                           (defaults to `'\\'`).
     *                         - `'clean'`: A boolean or array with instructions for `Text::clean()`.
     * @return string
     */
    public static function insert($str, $data, $options = [])
    {
        $options += ['before' => '{:', 'after' => '}', 'escape' => '\\', 'clean' => false];

        extract($options);

        $begin = $escape ? '(?<!' . preg_quote($escape) . ')' . preg_quote($before) : preg_quote($before);
        $end = preg_quote($options['after']);

        foreach ($data as $placeholder => $val) {
            $val = (is_array($val) || is_resource($val) || $val instanceof Closure) ? '' : $val;
            $val = (is_object($val) && !method_exists($val, '__toString')) ? '' : (string) $val;
            $str = preg_replace('/' . $begin . $placeholder . $end .'/', $val, $str);
        }
        if ($escape) {
            $str = preg_replace('/' . preg_quote($escape) . preg_quote($before) . '/', $before, $str);
        }
        return $options['clean'] ? static::clean($str, $options) : $str;
    }

    /**
     * Cleans up a `Text::insert()` formatted string with given `$options` depending
     * on the `'clean'` option. The goal of this function is to replace all whitespace
     * and unneeded mark-up around place-holders that did not get replaced by `Text::insert()`.
     *
     * @param  string $str     The string to clean.
     * @param  array  $options Available options are:
     *                         - `'before'`: characters marking the start of targeted substring.
     *                         - `'after'`: characters marking the end of targeted substring.
     *                         - `'escape'`: The character or string used to escape the before character or string
     *                           (defaults to `'\\'`).
     *                         - `'gap'`: Regular expression matching gaps.
     *                         - `'word'`: Regular expression matching words.
     *                         - `'replacement'`: String to use for cleaned substrings (defaults to `''`).
     * @return string          The cleaned string.
     */
    public static function clean($str, $options = [])
    {
        $options += [
            'before' => '{:',
            'after' => '}',
            'escape' => '\\',
            'word' => '[\w,.]+',
            'gap' => '(\s*(?:(?:and|or|,)\s*)?)',
            'replacement' => ''
        ];

        extract($options);

        $begin = $escape ? '(?<!' . preg_quote($escape) . ')' . preg_quote($before) : preg_quote($before);
        $end = preg_quote($options['after']);

        $callback = function($matches) use ($replacement) {
            if (isset($matches[2]) && isset($matches[3]) && trim($matches[2]) === trim($matches[3])) {
                if (trim($matches[2]) || ($matches[2] && $matches[3])) {
                    return $matches[2] . $replacement;
                }
            }
            return $replacement;
        };
        $str = preg_replace_callback('/(' . $gap. $before . $word . $after . $gap .')+/', $callback, $str);
        if ($escape) {
            $str = preg_replace('/' . preg_quote($escape) . preg_quote($before) . '/', $before, $str);
        }
        return $str;
    }

}