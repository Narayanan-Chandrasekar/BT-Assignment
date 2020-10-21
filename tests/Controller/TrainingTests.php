<?php
namespace App\Tests\Controller;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class createTrainingTest extends WebTestCase
{


    public $insertId;
    public function testAdd()
    {
        $client = static::createClient();
      
        $client->xmlHttpRequest('POST', '/addTraining/4', ['data' => 'topic=Test Module&description=Testing body!&start=2020-10-19&end=2020-10-20&duration=3&seats=1'
                                                  ]
                          );
        $this->assertTrue($client->getResponse()->isSuccessful());                            
    }

    public function testEdit()
    {
        $client = static::createClient();
      
        $client->xmlHttpRequest('POST', '/editTraining/4', ['data' => 'topic=Test ModuleEdited &description=Testing body!&start=2020-10-19&end=2020-10-20&duration=3&seats=1&trainingId=23'
                                                  ]
                          );
        
        $this->assertTrue($client->getResponse()->isSuccessful());                            
    }

    public function testDelete()
    {
        $client = static::createClient();
      
        $client->xmlHttpRequest('DELETE', '/deleteTraining/25', ['data' => 'topic=Test ModuleEdited &description=Testing body!&start=2020-10-19&end=2020-10-20&duration=3&seats=1'
                                                  ]
                          );
        
        $this->assertTrue($client->getResponse()->isSuccessful());                            
    }
    
}