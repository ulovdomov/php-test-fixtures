<?php declare(strict_types = 1);

namespace UlovDomov\TestFixtures;

final class Dumper
{
    private const MAX_LENGTH = 70;

    private const MAX_DEPTH = 10;

    /**
     * Dumps variable to JSON or serialize it.
     *
     * @param mixed $value variable to dump
     */
    public static function toString(mixed $value): string
    {
        try {
            return \json_encode($value, \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return '(serialized) ' . \base64_encode(\serialize($value));
        }
    }

    /**
     * Dumps variable in PHP format.
     *
     * @param mixed $var variable to dump
     */
    public static function toPhp(mixed $var): string
    {
        return self::convertToPhp($var);
    }

    private static function hash($object): string
    {
        return '#' . \substr(\md5(\spl_object_hash($object)), 0, 4);
    }

    private static function convertToPhp(mixed &$var, array &$list = [], int $level = 0, int &$line = 1): string
    {
        if (\is_float($var)) {
            $var = \str_replace(',', '.', "$var");

            return \strpos($var, '.') === false ? $var . '.0' : $var;
        } elseif (\is_bool($var)) {
            return $var ? 'true' : 'false';
        } elseif ($var === null) {
            return 'null';
        } elseif (\is_string($var) && (\preg_match('#[^\x09\x20-\x7E\xA0-\x{10FFFF}]#u', $var) || \preg_last_error())) {
            static $table;

            if ($table === null) {
                foreach (\array_merge(\range("\x00", "\x1F"), \range("\x7F", "\xFF")) as $ch) {
                    $table[$ch] = '\x' . \str_pad(\dechex(\ord($ch)), 2, '0', \STR_PAD_LEFT);
                }
                $table['\\'] = '\\\\';
                $table["\r"] = '\r';
                $table["\n"] = '\n';
                $table["\t"] = '\t';
                $table['$'] = '\$';
                $table['"'] = '\"';
            }

            return '"' . \strtr($var, $table) . '"';
        } elseif (\is_array($var)) {
            $space = \str_repeat("\t", $level);

            static $marker;

            if ($marker === null) {
                $marker = \uniqid("\x00", true);
            }

            if (empty($var)) {
                $out = '';

            } elseif ($level > self::MAX_DEPTH || isset($var[$marker])) {
                return '/* Nesting level too deep or recursive dependency */';
            } else {
                $out = "\n$space";
                $outShort = '';
                $var[$marker] = true;
                $oldLine = $line;
                $line++;
                $counter = 0;

                foreach ($var as $k => &$v) {
                    if ($k !== $marker) {
                        $item = ($k === $counter ? '' : self::convertToPhp(
                                    $k,
                                    $list,
                                    $level + 1,
                                    $line,
                                ) . ' => ') . self::convertToPhp(
                                $v,
                                $list,
                                $level + 1,
                                $line,
                            );
                        $counter = \is_int($k) ? \max($k + 1, $counter) : $counter;
                        $outShort .= ($outShort === '' ? '' : ', ') . $item;
                        $out .= "\t$item,\n$space";
                        $line++;
                    }
                }
                unset($var[$marker]);

                if (\strpos($outShort, "\n") === false && \strlen($outShort) < self::MAX_LENGTH) {
                    $line = $oldLine;
                    $out = $outShort;
                }
            }

            return '[' . $out . ']';
        } elseif ($var instanceof \Closure) {
            $rc = new \ReflectionFunction($var);

            return "/* Closure defined in file {$rc->getFileName()} on line {$rc->getStartLine()} */";
        } elseif (\is_object($var)) {
            $rc = new \ReflectionObject($var);

            if ($rc->isAnonymous()) {
                return "/* Anonymous class defined in file {$rc->getFileName()} on line {$rc->getStartLine()} */";
            }

            $arr = (array) $var;
            $space = \str_repeat("\t", $level);
            $class = $var::class;
            $used = &$list[\spl_object_hash($var)];

            if (empty($arr)) {
                $out = '';

            } elseif ($used) {
                return "/* $class dumped on line $used */";
            } elseif ($level > self::MAX_DEPTH) {
                return '/* Nesting level too deep */';
            } else {
                $out = "\n";
                $used = $line;
                $line++;

                foreach ($arr as $k => &$v) {
                    if (isset($k[0]) && $k[0] === "\x00") {
                        $k = \substr($k, \strrpos($k, "\x00") + 1);
                    }

                    $out .= "$space\t" . self::convertToPhp($k, $list, $level + 1, $line) . ' => ' . self::convertToPhp(
                            $v,
                            $list,
                            $level + 1,
                            $line,
                        ) . ",\n";
                    $line++;
                }
                $out .= $space;
            }

            $hash = self::hash($var);

            return $class === 'stdClass'
                ? "(object) /* $hash */ [$out]"
                : "$class::__set_state(/* $hash */ [$out])";
        } elseif (\is_resource($var)) {
            return '/* resource ' . \get_resource_type($var) . ' */';
        } else {
            $res = \var_export($var, true);
            $line += \substr_count($res, "\n");

            return $res;
        }
    }
}
