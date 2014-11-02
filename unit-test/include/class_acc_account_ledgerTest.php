<?php
/**
 * Generated by PHPUnit_SkeletonGenerator on 2014-10-31 at 20:33:22.
 * @backupGlobals enabled
 */
class Acc_Account_LedgerTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Acc_Account_Ledger
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     * 
     */
    protected function setUp()
    {
        global $g_connection, $g_parameter;
        $_REQUEST['gDossier']=DOSSIER;
        $g_connection=new Database(DOSSIER);
        $g_parameter=new Own($g_connection);
        $this->object=new Acc_Account_Ledger($g_connection, 400);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }

    /**
     * @covers Acc_Account_Ledger::get_row
     * @todo   Implement testGet_row().
     */
    public function testGet_row()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Acc_Account_Ledger::get_row_date
     * @todo   Implement testGet_row_date().
     */
    public function testGet_row_date()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }
    function dataGet_Name()
    {
        return array(
            array('10','Capital '),
            array('01','Poste inconnu')
        );
    }
    /**
     * @covers Acc_Account_Ledger::get_name
     * @todo   Implement testGet_name().
     * @dataProvider dataGet_Name
     */
    public function testGet_name($id,$result)
    {
      $this->object->id=$id;
      $this->assertEquals($this->object->get_name(),$result);
    }

    /**
     * @covers Acc_Account_Ledger::do_exist
     * @todo   Implement testDo_exist().
     * @dataProvider dataDo_exist
     */
    public function testDo_exist($p_value, $result)
    {
        $this->object->id=$p_value;
        $this->assertEquals($this->object->do_exist(), $result);
    }

    function dataDo_exist()
    {
        return array(
            array('400', 1),
            array('400A', 0),
            array('550', 1),
            array('60BXX', 0)
        );
    }

    /**
     * @covers Acc_Account_Ledger::load
     * @todo   Implement testLoad().
     */
    public function testLoad()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Acc_Account_Ledger::get
     * @todo   Implement testGet().
     */
    public function testGet()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Acc_Account_Ledger::get_solde
     * @todo   Implement testGet_solde().
     */
    public function testGet_solde()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Acc_Account_Ledger::get_solde_detail
     * @todo   Implement testGet_solde_detail().
     */
    public function testGet_solde_detail()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Acc_Account_Ledger::isTVA
     * @todo   Implement testIsTVA().
     */
    public function testIsTVA()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Acc_Account_Ledger::HtmlTable
     * @todo   Implement testHtmlTable().
     */
    public function testHtmlTable()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }
    public function dataGet_amount_side()
    {
        return array (
            array(0,"="), 
            array(1000,D),
            array(-1000,C),
        );
    }
    /**
     * @covers Acc_Account_Ledger::get_amount_side
     * @todo   Implement testGet_amount_side().
     * @dataProvider dataGet_amount_side
     */
    public function testGet_amount_side($amount,$result)
    {
       $this->assertEquals($this->object->get_amount_side($amount),$result);
    }

    /**
     * @covers Acc_Account_Ledger::HtmlTableHeader
     * @todo   Implement testHtmlTableHeader().
     */
    public function testHtmlTableHeader()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Acc_Account_Ledger::belong_ledger
     * @todo   Implement testBelong_ledger().
     * @dataProvider DataBelong_ledger
     */
    public function testBelong_ledger($p_jrn, $result)
    {
        $this->assertEquals($this->object->belong_ledger($p_jrn), $result);
    }

    function DataBelong_ledger()
    {
        return array(
            array(0, -1),
            array(1, -1),
            array(3, -1),
            array(2, 0),
            array(4, 0)
        );
    }
    public function dataGet_account_ledger()
    {
        return array(
            array(0,array()),
            array(1,array('5*','')),
            array(2,array('4*')),
            array(3,array('6*'))
        
        );
    }
    /**
     * @covers Acc_Account_Ledger::get_account_ledger
     * @todo   Implement testGet_account_ledger().
     * @dataProvider dataGet_account_ledger
     */
    public function testGet_account_ledger($p_jrn,$result)
    {
        echo "ledger $p_jrn";
        $this->assertEquals($this->object->get_account_ledger($p_jrn),$result);
        printf ("\n");
    }

    /**
     * @covers Acc_Account_Ledger::build_sql_account
     * @todo   Implement testBuild_sql_account().
     * @dataProvider DataBuild_Sql_account
     */
    public function testBuild_sql_account($p_jrn, $result)
    {
        print "\n--------------------\n";
        $value=$this->object->build_sql_account($p_jrn);
        printf(" ledger %s [%s]", $p_jrn, $value);
        $this->assertEquals(trim($value), $result);
    }

    public function DataBuild_Sql_account()
    {
        return array(
            array(0, ""),
            array(1, "pcm_val::text like '5%'"),
            array(2, "pcm_val::text like '4%'"),
            array(3, "pcm_val::text like '6%'"),
            array(4, "")
        );
    }

    function dataFind_Card()
    {
        return array(
            array('6191', '22'),
            array('6192', '23'),
            array('4400004', '25')
        );
    }

    /**
     * @covers Acc_Account_Ledger::find_card
     * @todo   Implement testFind_card().
     * @dataProvider dataFind_Card()
     */
    public function testFind_card($p_value, $p_card)
    {
        $this->object->id=$p_value;
        $result=$this->object->find_card();
        $this->assertEquals($p_card, $result[0]['f_id']);
    }

    /**
     * @covers Acc_Account_Ledger::test_me
     * @todo   Implement testTest_me().
     */
    public function testTest_me()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

}
