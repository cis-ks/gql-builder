<?php /** @noinspection PhpUnused */

namespace Cis\GqlBuilder;

use Cis\GqlBuilder\Parts\Argument;
use Cis\GqlBuilder\Parts\SelectionSet;
use Cis\GqlBuilder\Parts\Variable;
use InvalidArgumentException;
use RuntimeException;

class Query
{
    public const int QUERY_PRETTY_PRINT = 1;
    protected const string OPERATION_NAME = 'query';

    protected SelectionSet $selectionSet;
    protected array $arguments = [];
    protected array $variables = [];
    protected array $fragments = [];
    protected bool $nested = false;
    protected int $lineIndent = 4;
    protected int $flags = 0;

    public static function root(
        array $selectionSet = [],
        array $fragments = [],
        array $variables = [],
    ): static
    {
        return static::create(selectionSet: $selectionSet, variables: $variables)->setFragments(array_map(
            fn ($f) => $f instanceof Fragment ? $f : new Fragment(...$f),
            $fragments
        ));
    }

    public static function create(
        string $name = '',
        string $alias = '',
        array $selectionSet = [],
        array $variables = []
    ): static {
        return (new static($name, $alias))->setSelectionSet($selectionSet)->setVariables(array_map(
            fn ($v) => $v instanceof Variable ? $v : new Variable(...$v),
            $variables
        ));
    }

    public static function query(
        string $name,
        array $selectionSet,
        string $alias = '',
        array $arguments = [],
    ): static {
        return (new static($name, $alias))->setNested()->setSelectionSet($selectionSet)->setArguments(array_map(
            fn ($a) => $a instanceof Argument ? $a : new Argument(...$a),
            $arguments
        ));
    }

    public static function fragment(string $name, string $reference, array $selectionSet): Fragment
    {
        return (new Fragment($name, $reference))->setSelectionSet($selectionSet);
    }

    public function __construct(
        protected string $name = '',
        protected string $alias = '',
    ) {
    }

    public function __toString(): string
    {
        return $this->generateQuery();
    }

    public function setAlias(string $alias): Query
    {
        $this->alias = $alias;
        return $this;
    }

    public function setArguments(array $arguments): Query
    {
        $this->arguments = $arguments;
        return $this;
    }

    public function setFragments(array $fragments): Query
    {
        if (count(array_filter($fragments, fn ($f) => !($f instanceof Fragment))) > 0) {
            throw new InvalidArgumentException('Provided fragments should only contain Fragments!');
        }

        $this->fragments = $fragments;
        return $this;
    }

    public function setName(string $name): Query
    {
        $this->name = $name;
        return $this;
    }

    public function setSelectionSet(array $selectionSet): Query
    {
        $this->selectionSet = new SelectionSet($selectionSet);
        return $this;
    }
    public function setVariables(array $variables): Query
    {
        if (count(array_filter($variables, fn ($v) => !($v instanceof Variable))) > 0) {
            throw new InvalidArgumentException('Provided list should only contain Variables!');
        }
        $this->variables = $variables;
        return $this;
    }

    public function setNested(): Query
    {
        $this->nested = true;
        return $this;
    }

    public function setLineIndent(int $lineIndent): Query
    {
        if ($lineIndent % 2 == 0) {
            $this->lineIndent = $lineIndent;
        }
        return $this;
    }

    public function setOutputFlags(int $flags): Query
    {
        $this->flags = $flags;
        return $this;
    }

    protected function generateArguments(): string
    {
        if (count($this->arguments) == 0) {
            return '';
        }

        return '(' . implode(', ', $this->arguments) . ')';
    }

    protected function generateVariables(): string
    {
        if ($this->nested || count($this->variables) == 0) {
            return '';
        }

        return '(' . implode(', ', $this->variables) . ')';
    }

    protected function generateQuery(int|null $flags = null): string
    {
        if ($flags === null) {
            $flags = $this->flags;
        }

        if ($this->selectionSet->count() == 0) {
            throw new RuntimeException("Empty Selection-Sets are not supported");
        }

        $query = $this->nested
            ? sprintf(
                '%s%s%s{%s}',
                $this->alias !== '' ? $this->alias . ': ' : '',
                $this->name,
                $this->generateArguments(),
                $this->selectionSet
            )
            : $this->generateRootQuery();

        if (count($this->fragments) > 0) {
            $query .= PHP_EOL . implode(PHP_EOL, $this->fragments);
        }

        if (($flags & self::QUERY_PRETTY_PRINT) == self::QUERY_PRETTY_PRINT) {
            $query = $this->prettifyQuery($query);
        }

        return $query;
    }

    protected function generateRootQuery(): string
    {
        if ($this->name !== '' && $this->selectionSet->hasFields()) {
            return sprintf(
                '%s{%s%s{%s}}',
                static::OPERATION_NAME,
                $this->name,
                $this->generateVariables(),
                $this->selectionSet
            );
        } else {
            return sprintf(
                '%s%s%s{%s}',
                static::OPERATION_NAME,
                $this->name !== '' ? ' ' . $this->name : '',
                $this->generateVariables(),
                $this->selectionSet
            );
        }
    }

    protected function prettifyQuery(string $query): string
    {
        $newQuery = $this->splitQueryOnBrackets($query);
        $newQuery = $this->insertLineIntend($newQuery);
        $newQuery = $this->reformatQueryStringFields($newQuery);

        $newQuery = str_replace(
            ["\t", ":  {"],
            [str_repeat(" ", $this->lineIndent), ": {"],
            implode("\n", $newQuery)
        );

        return preg_replace('/([^(]+)\(/', '$1 (', $newQuery);
    }

    /**
     * @param string $query
     * @return array
     */
    private function splitQueryOnBrackets(string $query): array
    {
        $newQuery = explode("\n", str_replace(["{", "}"], [" {\n", "\n}\n"], $query));
        for ($i = 1; $i < count($newQuery); $i++) {
            if (str_contains($newQuery[$i], "{")) {
                if (str_contains($newQuery[$i - 1], "(") && !str_contains($newQuery[$i - 1], ")")) {
                    $newQuery[$i] = trim($newQuery[$i - 1], "\t") . $newQuery[$i];
                    unset($newQuery[$i - 1]);
                }
            } elseif (
                str_contains($newQuery[$i - 1], '{')
                && str_contains($newQuery[$i - 1], '(')
                && !str_contains($newQuery[$i - 1], ')')
            ) {
                $newQuery[$i] = $newQuery[$i - 1] . $newQuery[$i];
                unset($newQuery[$i - 1]);
            }
        }
        return array_filter($newQuery, fn ($q) => trim($q) !== '');
    }

    /**
     * @param array $newQuery
     * @return array
     */
    private function insertLineIntend(array $newQuery): array
    {
        $level = 0;
        foreach ($newQuery as $i => $value) {
            if (str_ends_with($value, '{')) {
                $newQuery[$i] = str_repeat("\t", $level) . $value;
                $level++;
            } elseif (str_ends_with($value, '}')) {
                $level--;
                $newQuery[$i] = str_repeat("\t", $level) . $value;
            } else {
                $newQuery[$i] = str_repeat("\t", $level) . $value;
            }
        }
        return $newQuery;
    }

    /**
     * @param array $query
     * @return array
     */
    private function reformatQueryStringFields(array $query): array
    {
        $reformattedQuery = [];

        foreach ($query as $i => $value) {
            if (preg_match('/^(\t+)([\w_-] ?)+$/', $value, $matches)) {
                $parts = array_map(fn($p) => $matches[1] . $p, explode(' ', ltrim($matches[0], "\t")));
                array_push($reformattedQuery, ...$parts);
            } elseif (preg_match('/^(\t+)([\w_-] ?)+({)?$/', $value, $matches)) {
                $parts = array_map(fn($p) => $matches[1] . $p, explode(' ' , trim($matches[0], "\t {")));
                if (array_key_exists(3, $matches) && $matches[3] == '{') {
                    $lastPart = array_pop($parts);
                    array_push($reformattedQuery, ...$parts);
                    $reformattedQuery[] = $lastPart . ' {';
                } else {
                    array_push($reformattedQuery, ...$parts);
                }
            } else {
                $reformattedQuery[] = $value;
            }
        }
        return $reformattedQuery;
    }
}