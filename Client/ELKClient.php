<?php
/**
 * Date: 7/07/18
 * Time: 10:17 PM
 */

namespace Berriart\Bundle\APMBundle\Client;

use PhilKra\Agent;
use PhilKra\Events\Transaction;

/**
 * Class ELKClient
 */
class ELKClient extends AbstractClient implements ClientInterface
{
    /** @var  Agent $client*/
    protected $client;

    /** @var Transaction $transaction */
    protected $transaction;

    /** @var array $spans */
    protected $spans = [];

    /**
     * @param array $config
     * @return ClientInterface|void
     */
    public function configure($config)
    {
        $this->client = new Agent($config);
    }

    /**
     * @return Agent
     */
    public function getOriginalClient()
    {
        return $this->client;
    }

    /**
     * @return bool
     */
    public function hasToThrowExceptions()
    {
        return false;
    }

    /**
     * @param \Exception $exception
     * @param array      $properties
     * @param array      $measurements
     *
     * @return BaseClientInterface|void
     */
    public function trackException(\Exception $exception, $properties = [], $measurements = [])
    {
        $this->client->captureThrowable($exception);
    }

    public function flush()
    {
        $this->transaction->setSpans($this->spans);
        $this->client->send();
    }

    public function trackMetric($name, $value, $properties = [])
    {
        $span = [];
        $span[$name] = $value;
        $span = $this->addDefaultProperties($span);
        array_push($this->spans, $span);
    }

    public function trackDependency(
        $name,
        $type = 0,
        $commandName = null,
        $startTime = null,
        $durationInMs = 0,
        $isSuccessful = true,
        $resultCode = null,
        $isAsync = null,
        $properties = []
    ) {
        $span = [];
        $span['name'] = $name;
        $span['type'] = $type;
        $span['commandName'] = $commandName;
        $span['startTime'] = $startTime;
        $span['durationInMs'] = $durationInMs;
        $span['isSuccessful'] = $isSuccessful;
        $span['resultCode'] = $resultCode;
        $span['isAsync'] = $isAsync;
        $span['properties'] = $properties;

        array_push($this->spans, $span);
    }

    public function trackEvent($name, $properties = [], $measurements = [])
    {
        $span = [];
        $span['name'] = $name;
        $span['properties'] = $properties;
        $span['properties'] = $this->addDefaultProperties($span['properties']);
        array_push($this->spans, $span);
    }

    public function trackMessage($message, $properties = [])
    {
        $properties = $this->addDefaultProperties($properties);

        $this->trackEvent($message, $properties);
    }

    public function trackRequest($name, $url, $startTime, $duration, $properties = [], $measurements = [])
    {
        $this->client->startTransaction($url);
    }
}
