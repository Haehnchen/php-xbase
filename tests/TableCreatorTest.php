<?php declare(strict_types=1);

namespace XBase\Tests;

use XBase\Enum\Codepage;
use XBase\Enum\FieldType;
use XBase\Enum\TableFlag;
use XBase\Enum\TableType;
use XBase\Exception\ColumnException;
use XBase\Exception\XBaseException;
use XBase\Header\Column;
use XBase\Header\HeaderFactory;
use XBase\TableCreator;
use XBase\TableEditor;
use XBase\TableReader;
use XBase\Tests\TableCreator\CleanupTrait;

/**
 * Common test cases
 */
class TableCreatorTest extends AbstractTestCase
{
    use CleanupTrait;

    /**
     * Creating dBaseIII file with memo and checks header.
     */
    public function testCreateDbase3(): string
    {
        $filepath = $this->cleanupFiles(self::$NEW_FILEPATH);
        $header = HeaderFactory::create(TableType::DBASE_III_PLUS_MEMO);

        $tableCreator = new TableCreator($filepath, $header);
        $tableCreator
            ->addColumn(new Column([
                'name'   => 'name',
                'type'   => FieldType::CHAR,
                'length' => 20,
            ]))
            ->addColumn(new Column([
                'name'   => 'birthday',
                'type'   => FieldType::DATE,
            ]))
            ->addColumn(new Column([
                'name'   => 'is_man',
                'type'   => FieldType::LOGICAL,
            ]))
            ->addColumn(new Column([
                'name'   => 'bio',
                'type'   => FieldType::MEMO,
            ]))
            ->addColumn(new Column([
                'name'         => 'money',
                'type'         => FieldType::NUMERIC,
                'length'       => 20,
                'decimalCount' => 4,
            ]))
            ->addColumn(new Column([
                'name'   => 'image',
                'type'   => FieldType::MEMO,
            ]))
            ->save();

        $table = new TableReader($filepath);

        self::assertSame(6, $table->getColumnCount());
        self::assertSame(0, $table->getRecordCount());

        self::assertSame(TableType::DBASE_III_PLUS_MEMO, $table->getVersion());
        self::assertSame(Codepage::UNDEFINED, $table->getCodepage());
        self::assertSame(false, $table->isFoxpro());
        self::assertSame(225, $table->getHeaderLength());
        self::assertSame(70, $table->getRecordByteLength());
        self::assertSame(false, $table->isInTransaction());
        self::assertSame(false, $table->isEncrypted());
        self::assertSame(TableFlag::NONE, ord($table->getMdxFlag()));
        self::assertSame(0, $table->getLanguageCode());

        //<editor-fold desc="columns">
        $columns = $table->getColumns();
        $column = $columns['name'];
        self::assertSame(FieldType::CHAR, $column->getType());
        self::assertSame(1, $column->getBytePos());
        self::assertSame(20, $column->getLength());
        $column = $columns['birthday'];
        self::assertSame(FieldType::DATE, $column->getType());
        self::assertSame(21, $column->getBytePos());
        self::assertSame(8, $column->getLength());
        $column = $columns['is_man'];
        self::assertSame(FieldType::LOGICAL, $column->getType());
        self::assertSame(29, $column->getBytePos());
        self::assertSame(1, $column->getLength());
        $column = $columns['bio'];
        self::assertSame(FieldType::MEMO, $column->getType());
        self::assertSame(30, $column->getBytePos());
        self::assertSame(10, $column->getLength());
        $column = $columns['money'];
        self::assertSame(FieldType::NUMERIC, $column->getType());
        self::assertSame(40, $column->getBytePos());
        self::assertSame(20, $column->getLength());
        self::assertSame(4, $column->getDecimalCount());
        $column = $columns['image'];
        self::assertSame(FieldType::MEMO, $column->getType());
        self::assertSame(60, $column->getBytePos());
        self::assertSame(10, $column->getLength());
        //</editor-fold>

        return $filepath;
    }

    /**
     * @depends testCreateDbase3
     *
     * @throws \Exception
     */
    public function testWriteDataIntoCreatedDbase3(string $filepath): void
    {
        $imgPath = __DIR__.'/Resources/img';

        $grootBio = <<<TEXT
Groot (/?ru?t/) is a fictional character appearing in American comic books published by Marvel Comics. Created by Stan Lee, Larry Lieber and Jack Kirby, the character first appeared in Tales to Astonish #13 (November 1960). An extraterrestrial, sentient tree-like creature, the original Groot first appeared as an invader that intended to capture humans for experimentation.

The character was reintroduced as a heroic, noble being in 2006, and appeared in the crossover comic book storyline "Annihilation: Conquest". Groot went on to star in its spin-off series, Guardians of the Galaxy, joining the team of the same name. Groot has been featured in a variety of associated Marvel merchandise, including animated television series, toys and trading cards. Vin Diesel voices Groot in the Marvel Cinematic Universe films Guardians of the Galaxy (2014), Guardians of the Galaxy Vol. 2 (2017), Avengers: Infinity War (2018), and Avengers: Endgame (2019), while Krystian Godlewski played the character via performance capture in the first film. Fred Tatasciore voices Groot on the Disney California Adventure ride Guardians of the Galaxy: Mission Breakout. Diesel will return to voice the character in Guardians of the Galaxy Vol. 3. Diesel also voiced Groot as a cameo in the 2018 Disney animated film Ralph Breaks the Internet. Since his film premiere and animated series debut, Groot has become a pop culture icon, with his repeated line "I am Groot" becoming an Internet meme. 
TEXT;
        $rocketBio = <<<TEXT
Rocket Raccoon is a fictional character appearing in American comic books published by Marvel Comics. Created by writer Bill Mantlo and artist Keith Giffen, the character first appeared in Marvel Preview #7 (Summer 1976). He is an intelligent, anthropomorphic raccoon, who is an expert marksman, weapon specialist and master tactician. His name and aspects of his character are a nod to The Beatles' 1968 song "Rocky Raccoon". Rocket Raccoon appeared as a prominent member in the 2008 relaunch of the superhero team Guardians of the Galaxy.

The character has appeared in several media adaptations as a member of that team, including animated television series, toys and video games. He appears in the Marvel Cinematic Universe films Guardians of the Galaxy (2014), Guardians of the Galaxy Vol. 2 (2017), Avengers: Infinity War (2018), and Avengers: Endgame (2019). In these appearances, Rocket Raccoon is voiced by Bradley Cooper, with motion capture provided by Sean Gunn. 
TEXT;

        $starLordBio = <<<TEXT
Star-Lord (Peter Jason Quill) is a fictional superhero appearing in American comic books published by Marvel Comics. The character, created by Steve Englehart and Steve Gan, first appeared in Marvel Preview #4 (January 1976). The son of human Meredith Quill and Spartoi J'son, Peter Quill assumes the mantle of Star-Lord, an interplanetary policeman.

The character played prominent roles in the comic book storylines "Annihilation" (2006) and "Annihilation: Conquest" (2007), "War of Kings" (2008), and The Thanos Imperative (2009). He became the leader of the space-based superhero team Guardians of the Galaxy in the 2008 relaunch of the comic of the same name. He has been featured in a variety of associated Marvel merchandise, including animated television series, toys and trading cards.

Chris Pratt portrays the character in the Marvel Cinematic Universe films Guardians of the Galaxy (2014), Guardians of the Galaxy Vol. 2 (2017), Avengers: Infinity War (2018), and Avengers: Endgame (2019). Wyatt Oleff portrays a young Peter Quill in the first two Guardians of the Galaxy films. Pratt will return to play the character in Guardians of the Galaxy Vol. 3.
TEXT;

        $table = new TableEditor($filepath);

        $record = $table->appendRecord()
            ->set('name', 'Groot')
            ->set('birthday', new \DateTime('1960-11-01'))
            ->set('is_man', false)
            ->set('bio', $grootBio)
            ->set('money', 12.1235)
            ->set('image', file_get_contents("$imgPath/groot.jpeg"));
        $table->writeRecord($record);

        $record = $table->appendRecord()
            ->set('name', 'Rocket Raccoon')
            ->set('birthday', new \DateTime('1976-06-01'))
            ->set('is_man', false)
            ->set('bio', $rocketBio)
            ->set('money', 325.32)
            ->set('image', file_get_contents("$imgPath/rocket_raccoon.png"));
        $table->writeRecord($record);

        $record = $table->appendRecord()
            ->set('name', 'Star-Lord')
            ->set('birthday', new \DateTime('1976-01-01'))
            ->set('is_man', true)
            ->set('bio', $starLordBio)
            ->set('money', 0.0)
            ->set('image', file_get_contents("$imgPath/star_lord.png"));
        $table->writeRecord($record);

        $table
            ->save()
            ->close();

        $this->assertRecords(new TableReader($filepath));
    }

    /**
     * @dataProvider dataAddInvalidColumn
     */
    public function testAddInvalidColumn(Column $column): void
    {
        self::expectException(ColumnException::class);

        $filepath = $this->cleanupFiles(self::$NEW_FILEPATH);
        $header = HeaderFactory::create(TableType::DBASE_III_PLUS_NOMEMO);
        $table = new TableCreator($filepath, $header);
        $table
            ->addColumn($column)
            ->save();
    }

    public function dataAddInvalidColumn(): iterable
    {
        yield [new Column()];
        yield [new Column(['foo' => 'bar'])];
        yield [new Column(['type' => null])];
    }

    /**
     * @dataProvider dataColumnNotSupported
     */
    public function testColumnNotSupported(int $version, string $type): void
    {
        self::expectException(XBaseException::class);

        $filepath = $this->cleanupFiles(self::$NEW_FILEPATH);

        $header = HeaderFactory::create($version);
        $tableCreator = new TableCreator($filepath, $header);
        $tableCreator
            ->addColumn(new Column([
                'name' => 'test',
                'type' => $type,
            ]))
            ->save();
    }

    public function dataColumnNotSupported(): iterable
    {
        yield [TableType::DBASE_III_PLUS_MEMO, FieldType::DBASE7_DOUBLE];
        yield [TableType::DBASE_III_PLUS_NOMEMO, FieldType::GENERAL];
    }

    /**
     * Temp file should be removed
     * @see https://github.com/luads/php-xbase/issues/110
     */
    public function testCreateAsTempFile()
    {
        $header = HeaderFactory::create(TableType::DBASE_III_PLUS_MEMO);

        $tableCreator = new TableCreator('php://temp', $header);

        $tableCreator
            ->addColumn(new Column([
                'name'   => "name",
                'type'   => FieldType::CHAR,
                'length' => 20,
            ]))
            ->addColumn(new Column([
                'name' => "birthday",
                'type' => FieldType::DATE,
            ]))
            ->addColumn(new Column([
                'name' => "is_man",
                'type' => FieldType::LOGICAL,
            ]))
            ->addColumn(new Column([
                'name' => "bio",
                'type' => FieldType::MEMO,
            ]))
            ->addColumn(new Column([
                'name'         => "money",
                'type'         => FieldType::NUMERIC,
                'length'       => 20,
                'decimalCount' => 4,
            ]));

        //todo $tableCreator->save();

        self::assertTrue(true);
    }
}
