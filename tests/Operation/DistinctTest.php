<?php

namespace MongoDB\Tests\Operation;

use MongoDB\BSON\PackedArray;
use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Exception\InvalidArgumentException;
use MongoDB\Operation\Distinct;
use PHPUnit\Framework\Attributes\DataProvider;
use TypeError;

class DistinctTest extends TestCase
{
    #[DataProvider('provideInvalidDocumentValues')]
    public function testConstructorFilterArgumentTypeCheck($filter): void
    {
        $this->expectException($filter instanceof PackedArray ? InvalidArgumentException::class : TypeError::class);
        new Distinct($this->getDatabaseName(), $this->getCollectionName(), 'x', $filter);
    }

    #[DataProvider('provideInvalidConstructorOptions')]
    public function testConstructorOptionTypeChecks(array $options): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Distinct($this->getDatabaseName(), $this->getCollectionName(), 'x', [], $options);
    }

    public static function provideInvalidConstructorOptions()
    {
        return self::createOptionDataProvider([
            'collation' => self::getInvalidDocumentValues(),
            'hint' => self::getInvalidHintValues(),
            'maxTimeMS' => self::getInvalidIntegerValues(),
            'readConcern' => self::getInvalidReadConcernValues(),
            'readPreference' => self::getInvalidReadPreferenceValues(),
            'session' => self::getInvalidSessionValues(),
            'typeMap' => self::getInvalidArrayValues(),
        ]);
    }

    public function testExplainableCommandDocument(): void
    {
        $options = [
            'collation' => ['locale' => 'fr'],
            'hint' => '_id_',
            'maxTimeMS' => 100,
            'readConcern' => new ReadConcern(ReadConcern::LOCAL),
            'comment' => 'explain me',
            // Intentionally omitted options
            'readPreference' => new ReadPreference(ReadPreference::SECONDARY_PREFERRED),
            'typeMap' => ['root' => 'array'],
        ];
        $operation = new Distinct($this->getDatabaseName(), $this->getCollectionName(), 'f', ['x' => 1], $options);

        $expected = [
            'distinct' => $this->getCollectionName(),
            'key' => 'f',
            'query' => (object) ['x' => 1],
            'collation' => (object) ['locale' => 'fr'],
            'hint' => '_id_',
            'comment' => 'explain me',
            'maxTimeMS' => 100,
            'readConcern' => new ReadConcern(ReadConcern::LOCAL),
        ];
        $this->assertEquals($expected, $operation->getCommandDocument());
    }
}
