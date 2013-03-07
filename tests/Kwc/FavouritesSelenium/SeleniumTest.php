<?php
/**
 * @group slow
 * @group seleniuim
 */
class Kwc_FavouritesSelenium_SeleniumTest extends Kwf_Test_SeleniumTestCase
{
    protected $presetModel;
    public function setUp()
    {
        parent::setUp();
        Kwf_Component_Data_Root::setComponentClass('Kwc_FavouritesSelenium_Root');

        //use custom user model
        $this->presetModel = Kwf_Registry::get('config')->user->model;
        Kwf_Registry::get('config')->user->model = 'Kwc_FavouritesSelenium_UserModel';

        //unset existing userModel instance to get new one
        $reg = Kwf_Registry::getInstance()->set('userModel',
            Kwf_Model_Abstract::getInstance('Kwc_FavouritesSelenium_UserModel')
        );
    }

    public function tearDown()
    {
        parent::tearDown();
        Kwf_Registry::getInstance()->offsetUnset('config'); //re-reads config, undoes changes to config done above
        Kwf_Registry::getInstance()->offsetUnset('userModel');
        Kwf_Registry::get('config')->user->model = $this->presetModel;
    }

    public function testJavaScriptAndPersistence()
    {
        $this->openKwc('/selenium');
        $this->assertText('css=.kwcFavouritesPageComponentFavouritesCount', '2');

        // click on fav-icon to favourise
        $this->click("css=.switchLink > a");
        $this->assertText('css=.kwcFavouritesPageComponentFavouritesCount', '3');

        //reload to check if persistent
        $this->openKwc('/selenium');
        $this->assertText('css=.kwcFavouritesPageComponentFavouritesCount', '3');

        // click on fav-icon to defavourise
        $this->click("css=.switchLink > a");
        $this->assertText('css=.kwcFavouritesPageComponentFavouritesCount', '2');

        //reload to check if persistent
        $this->openKwc('/selenium');
        $this->assertText('css=.kwcFavouritesPageComponentFavouritesCount', '2');
    }
}
