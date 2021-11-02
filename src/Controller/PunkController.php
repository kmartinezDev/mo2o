<?php 

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Guzzle\Http\Client;

class PunkController extends AbstractController 
{

    private $client;
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->client = new Client('https://api.punkapi.com');
        $this->logger = $logger;
    }

    /**
     * @Route("/food", name="get_food")
     */
    public function finder(Request $request) 
    {
        $filter = $request->get('filter', '');
        return $this->getData('/v2/beers', $filter, ['id','name','description'], 'finderStructure');
    }

    /**
     * @Route("/detail", name="get_details")
     */
    public function detail(Request $request) 
    {
        return $this->getData('/v2/beers', null, ['id','name','description', 'image_url', 'tagline', 'first_brewed'], 'detailStructure');
    }

    private function getData($url, $filter = null, $param = null, $structure = null)
    {

        $response = new JsonResponse();
        
        try {
            $options = [];

            if($filter){
                $options['query'] = ['food' => $filter];
            }

            $request = $this->client->get($url, $headers = null, $options);
            $res = $request->send();

            if($structure){
                $res = $this->{$structure}($res, $param);
            } else {
                $res = json_decode($res->getBody()); 
            }
    
            return $response->setData([
                'success' => true,
                'data' => $res
            ]);
        } catch (\Exception $e) {

            $this->logger->error($e);

            return $response->setData([
                'error' => 'Error al obtener los datos de la api'
            ]);
        }
    }

    private function finderStructure($res, $param) : array 
    {
        
        $res = json_decode($res->getBody());
        $data = [];

        foreach ($res as $key => $row) {
            $data[$key][$param[0]] = $row->{$param[0]};
            $data[$key][$param[1]] = $row->{$param[1]};
            $data[$key][$param[2]] = $row->{$param[2]};
        }

        return $data;
    }

    private function detailStructure($res, $param) : array 
    {

        $res = json_decode($res->getBody());
        $data = [];

        foreach ($res as $key => $row) {
            $data[$key][$param[0]] = $row->{$param[0]};
            $data[$key][$param[1]] = $row->{$param[1]};
            $data[$key][$param[2]] = $row->{$param[2]};
            $data[$key][$param[3]] = $row->{$param[3]};
            $data[$key][$param[4]] = $row->{$param[4]};
            $data[$key][$param[5]] = $row->{$param[5]};
        }
        return $data;
    }
}

?>