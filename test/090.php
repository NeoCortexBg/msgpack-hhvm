<?php
if(!extension_loaded('msgpack'))
{
    dl('msgpack.' . PHP_SHLIB_SUFFIX);
}

error_reporting(0);

function test($type, $variable, $object, $result = null)
{
    $serialized = msgpack_pack($variable);
    $unserialized = msgpack_unpack($serialized, $object);
    var_dump($unserialized);
    if ($result)
    {
        echo $unserialized == $result ? 'OK' : 'ERROR', PHP_EOL;
    }
    else
    {
        echo 'SKIP', PHP_EOL;
    }
}

class Obj
{
    public $a;
    protected $b;
    private $c;

    public function __construct($a = null, $b = null, $c = null, $d = null)
    {
        $this->a = $a;
        $this->b = $b;
        $this->c = $c;
        if (is_array($d))
        {
            foreach ($d as $key => $val)
            {
                $this->{$key} = $val;
            }
        }
    }
}

test('null', null, 'Obj', new Obj(null, null, null));

test('bool: true', true, 'Obj', new Obj(true, null, null));
test('bool: false', false, 'Obj', new Obj(false, null, null));

test('zero: 0', 0, 'Obj', new Obj(0, null, null));
test('small: 1', 1, 'Obj', new Obj(1, null, null));
test('small: -1', -1, 'Obj', new Obj(-1, null, null));
test('medium: 1000', 1000, 'Obj', new Obj(1000, null, null));
test('medium: -1000', -1000, 'Obj', new Obj(-1000, null, null));
test('large: 100000', 100000, 'Obj', new Obj(100000, null, null));
test('large: -100000', -100000, 'Obj', new Obj(-100000, null, null));

test('double: 123.456', 123.456, 'Obj', new Obj(123.456, null, null));

test('empty: ""', "", 'Obj', new Obj("", null, null));
test('string: "foobar"', "foobar", 'Obj', new Obj("foobar", null, null));

test('array: empty', array(), 'Obj', new Obj(null, null, null));
test('array(1, 2, 3)', array(1, 2, 3), 'Obj', new Obj(1, 2, 3));
test('array(array(1, 2, 3), arr...', array(array(1, 2, 3), array(4, 5, 6), array(7, 8, 9)), 'Obj', new Obj(array(1, 2, 3), array(4, 5, 6), array(7, 8, 9)));
test('array(1, 2, 3, 4)', array(1, 2, 3, 4), 'Obj');

test('array("foo", "foobar", "foohoge")', array("foo", "foobar", "hoge"), 'Obj', new Obj("foo", "foobar", "hoge"));
test('array("a" => 1, "b" => 2))', array("a" => 1, "b" => 2), 'Obj', new Obj(1, 2, null));
test('array("one" => 1, "two" => 2))', array("one" => 1, "two" => 2), 'Obj', new Obj(null, null, null, array("one" => 1, "two" => 2)));
test('array("" => "empty")', array("" => "empty"), 'Obj');

test('array("a" => 1, "b" => 2, 3))', array("a" => 1, "b" => 2, 3), 'Obj', new Obj(1, 2, 3));
test('array(3, "a" => 1, "b" => 2))', array(3, "a" => 1, "b" => 2), 'Obj', new Obj(1, 2, 3));
test('array("a" => 1, 3, "b" => 2))', array("a" => 1, 3, "b" => 2), 'Obj', new Obj(1, 2, 3));

$a = array('foo');
test('array($a, $a)', array($a, $a), 'Obj', new Obj($a, $a, null));
test('array(&$a, &$a)', array(&$a, &$a), 'Obj', new Obj($a, $a, null));

test('array(&$a, $a)', array($a, &$a), 'Obj', new Obj($a, $a, null));
test('array(&$a, $a)', array(&$a, $a), 'Obj', new Obj($a, $a, null));

$a = array(
    'a' => array(
        'b' => 'c',
        'd' => 'e'
        ),
    'f' => array(
        'g' => 'h'
        )
    );
test('array', $a, 'Obj', new Obj(null, null, null, $a));

$o = new Obj(1, 2, 3);
test('object', $o, 'Obj', new Obj(1, 2, 3));

class Obj2 {
    public $A;
    protected $B;
    private $C;

    function __construct($a, $b, $c) {
        $this->A = $a;
        $this->B = $b;
        $this->C = $c;
    }
}

$o = new Obj2(1, 2, 3);
test('object', $o, 'Obj', new Obj($o));

$o1 = new Obj2(1, 2, 3);
$o2 = new Obj2(4, 5, 6);
test('object', array($o1, $o2), 'Obj', new Obj($o1, $o2));

$o = new Obj2(1, 2, 3);
test('object', array(&$o, &$o), 'Obj', new Obj($o, $o));

$o = new Obj2(1, 2, 3);
test('object', array(&$o, $o), 'Obj', new Obj($o, $o));

$o = new Obj2(1, 2, 3);
test('object', array($o, &$o), 'Obj', new Obj($o, $o));
