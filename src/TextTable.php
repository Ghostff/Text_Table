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
    private $last_row = 1;
    private $last_col = 1;


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
     * @param int|null          $width
     * @param int               $align
     * @param int               $padding
     * @return \TextTable
     */
    public function put($value, array $position = null, int $width = null, bool $wrap = true, int $align = self::ALIGN_LEFT, int $padding = 0): TextTable
    {
        if ($position) {
            [$this->last_row, $this->last_col] = $position;
        } else {
            if ($this->count % $this->column_size == 0) {
                $this->last_row++;
                $this->last_col = 1;
            } else {
                $this->last_col++;
            }
        }

        if ($value instanceof TextTable) {
            $length = strlen($value->lines[0]);
        } else {
            if ($width) {
                if ($wrap) {
                    array_map(function (string $chunk) use ($width) {
                        $this->put($chunk, [$this->last_row + 1, $this->last_col], $width, false);
                    }, str_split($value, $width));
                    return $this;
                }

                $value  = substr($value, 0, $width);
                $length = $width;
            } else {
                $length = strlen($value);
            }
        }

        $indexes                        = $this->last_col;
        $this->stack[$this->last_row][] = $this->createColumn($value, $align, $padding, $indexes);
        $this->maxes[$indexes]          = isset($this->maxes[$indexes]) ? max($this->maxes[$indexes], $length) : $length;
        $this->count++;

        return $this;
    }

    public function __toString(): string
    {
        $space       = ' ';
        $new_line    = "\n";
        $content     = null;
        $border      = null;
        $table_width = 0;

        foreach ($this->stack as $columns)
        {
            # Equalize columns. Fill up incomplete array eg if max column is 6 and 4 columns is specified,
            #  then populate the last 2 with an empty data.
            $remainder = count($columns) % $this->column_size;
            if ($remainder != 0) {
                $remainder = $this->column_size - $remainder;
                for ($i = 1; $i <= $remainder; $i++) {
                    $columns[] = $this->createColumn('', self::ALIGN_LEFT, 0, $this->last_col + $i);
                }
            }

            $line = '';
            foreach ($columns as $column)
            {
                $value      = $column['v'];
                $padding    = str_repeat($space, $column['p']);
                $pad_length = $this->maxes[$column['c']];

                if ($value instanceof TextTable) {
                    $new_value = rtrim($value->__toString(), $new_line);
                } else {
                    $new_value = $value;
                }

                $line .= "|{$padding}" . str_pad($new_value, $pad_length, $space, $column['a']);
            }

            # ?? to generate border once.
            $border  = $border ?? '+' . str_repeat('-', ($table_width = strlen($line) - 1)) . '+';
            $content .= $content ? "\n{$line}|\n{$border}" : "{$border}\n{$line}|\n{$border}";
        }

        $caption = $this->caption ? str_pad($this->caption, $table_width, $space, STR_PAD_BOTH) . $new_line : '';

        return "{$caption}{$content}";
    }

    /**
     * @param     $value
     * @param int $align
     * @param int $padding
     * @param int $column
     * @return array
     */
    private function createColumn($value, int $align, int $padding, int $column): array
    {
        return [
            'v' => $value,
            'a' => $align,
            'c' => $column,
            'p' => $padding,
        ];
    }
}
