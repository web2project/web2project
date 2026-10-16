<?php
/**
 * Reads the translation (.inc) files without eval().
 *
 * A translation file is a list of PHP array entries, one per line:
 *
 *     'English text',
 *     'key'=>'Translated text',
 *
 * Earlier versions wrapped the concatenated files in "array(...)" and passed
 * the result to eval(), so anything written into a .inc file ran as PHP code.
 * This parser uses PHP's own tokenizer and accepts only quoted string
 * literals, "=>" and commas (plus whitespace and comments). Any other token
 * makes that one entry invalid; it is skipped instead of being executed.
 *
 * @package web2project\utilities
 * @see     https://github.com/web2project/web2project/issues/313
 */
class w2p_Utilities_Translations
{
    /**
     * Parses the content of one or more concatenated .inc files.
     *
     * @param string $buffer
     *
     * @return array<int|string, string> the same array eval() produced, without
     *                                   the trailing '0' sentinel it needed
     */
    public static function parse($buffer)
    {
        $result = array();
        $key = null;
        $value = null;
        $arrow = false;
        $valid = true;

        foreach (token_get_all("<?php\n" . $buffer) as $token) {
            $id = is_array($token) ? $token[0] : $token;

            if (T_OPEN_TAG === $id || T_WHITESPACE === $id || T_COMMENT === $id || T_DOC_COMMENT === $id) {
                continue;
            }

            if (',' === $id) {
                if ($valid && null !== $key && (!$arrow || null !== $value)) {
                    if ($arrow) {
                        $result[$key] = $value;
                    } else {
                        $result[] = $key;
                    }
                }
                $key = null;
                $value = null;
                $arrow = false;
                $valid = true;
                continue;
            }

            if (T_CONSTANT_ENCAPSED_STRING === $id) {
                if (!$arrow && null === $key) {
                    $key = self::unquote($token[1]);
                } elseif ($arrow && null === $value) {
                    $value = self::unquote($token[1]);
                } else {
                    $valid = false;
                }
            } elseif (T_DOUBLE_ARROW === $id && !$arrow && null !== $key) {
                $arrow = true;
            } else {
                // Anything else (a function call, a variable, a concatenation...)
                // is not a translation entry.
                $valid = false;
            }
        }

        // A final entry without a trailing comma.
        if ($valid && null !== $key && (!$arrow || null !== $value)) {
            if ($arrow) {
                $result[$key] = $value;
            } else {
                $result[] = $key;
            }
        }

        return $result;
    }

    /**
     * Reads one or more .inc files and parses their combined content.
     *
     * @param array<int, string> $files
     *
     * @return array<int|string, string>
     */
    public static function parseFiles(array $files)
    {
        $buffer = '';
        foreach ($files as $file) {
            if (is_file($file)) {
                $buffer .= file_get_contents($file) . "\n";
            }
        }

        return self::parse($buffer);
    }

    /**
     * Returns the value of a PHP string literal token, as PHP would.
     *
     * @param string $literal
     *
     * @return string
     */
    protected static function unquote($literal)
    {
        $quote = $literal[0];
        $body = substr($literal, 1, -1);

        if ("'" === $quote) {
            return strtr($body, array('\\\\' => '\\', "\\'" => "'"));
        }

        // A double-quoted literal with no variables in it (otherwise the
        // tokenizer would not have returned T_CONSTANT_ENCAPSED_STRING).
        return stripcslashes($body);
    }
}
