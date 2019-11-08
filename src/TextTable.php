<?php


class TextTable
{
    public const ALIGN_CENTER = STR_PAD_BOTH;
    public const ALIGN_LEFT = STR_PAD_RIGHT;
    public const ALIGN_RIGHT = STR_PAD_LEFT;

    private $stack = [];
    private $count = 0;
    private $maxes = [];
    private $column_size = 0;
    private $caption = null;
    private $lines = [];

    public function __construct($column_size = 0)
    {
        $this->column_size = $column_size;
    }

    public function caption(string $table_caption): TextTable
    {
        $this->caption = $table_caption;

        return $this;
    }

    public function padding(int $size): TextTable
    {
        $this->stack[$this->count - 1]['p'] = $size;

        return $this;
    }

    public function align(int $position): TextTable
    {
        $this->stack[$this->count - 1]['a'] = $position;

        return $this;
    }

    public function position(int $row, int $column): TextTable
    {
        $index                    = $this->count - 1;
        $this->stack[$index]['r'] = $row;
        $this->stack[$index]['c'] = $column;

        return $this;
    }


    /**
     * @param \TextTable|string $value
     * @param string|null       $position
     * @param int               $align
     * @param int               $padding
     * @return \TextTable
     */
    public function put($value, string $position = null, int $align = self::ALIGN_LEFT, int $padding = 1): TextTable
    {
        $this->count++;
        $length = strlen($value);
        $column = 1;
        $row    = 1;

        if ($position) {
            [$row, $column] = array_map(function (string $value): int {
                return (int) trim($value);
            }, explode(',', $position));
        } else {
            if ($this->column_size > 0) {
                $column = ($this->count % $this->column_size == 0) ? $this->column_size : 1;
                $row    = ($this->count > $this->column_size) ? round($this->count / $this->column_size) : 1;
            }
        }

        if ($value instanceof TextTable) {
            $length = strlen($value->lines[0]);
        }

        $this->stack[] = [
            'v' => $value,
            'r' => $row,
            'c' => $column,
            'a' => $align,
            'p' => $padding,
            'l' => $length,
        ];

        $this->maxes[$column] = isset($this->maxes[$column]) ? max($this->maxes[$column], $length) : 0;

        return $this;
    }

    public function __toString(): string
    {
        $current_row = 0;
        $lines       = [];
        $input       = '';
        $space       = ' ';
        $new_line    = "\n";
        $border      = null;

        foreach ($this->stack as $data)
        {
            $value      = $data['v'];
            $padding    = str_repeat($space, $data['p']);
            $pad_length = $this->maxes[$data['c']] + 3;

            if ($value instanceof TextTable) {
                $new_value = rtrim($value->__toString(), $new_line);
            } else {
                $new_value = $value;
            }

            $input .= "|{$padding}" . str_pad($new_value, $pad_length, $space, $data['a']) . $padding;

            if ($data['c'] == $this->column_size) {
                $input .= '|';
            }

            if (($current_row + 1) != $data['r']) {
                $border  = $border ?? '+' . str_repeat('-', strlen($input) - 2) . '+';

                if (empty($lines)) {
                    $lines[] = $border;
                }

                $lines[] = $input;
                $lines[] = $border;
                $input   = '';
            }

            $current_row = $data['r'];
        }

        $this->lines = $lines;
        $caption     = $this->caption ? str_pad($this->caption, strlen($lines[0]), $space, STR_PAD_BOTH) . $new_line : '';

        return $caption . implode($new_line, $lines);
    }
}
