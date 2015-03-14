<?php

  class RestServer
	{
		public $serviceClass;
	
		public function __construct($serviceClass)
		{
			$this->serviceClass = $serviceClass;
		}
	
		public function handle()
		{
			if (array_key_exists("method", array_change_key_case($_REQUEST, CASE_LOWER)))
			{
				$rArray = array_change_key_case($_REQUEST, CASE_LOWER);
				$method = $rArray["method"];
	
				if (method_exists($this->serviceClass, $method))
				{
					$ref = new ReflectionMethod($this->serviceClass, $method);
					$params = $ref->getParameters();
					$pCount = count($params);
					$pArray = array();
					$paramStr = "";
					
					$i = 0;
					
					foreach ($params as $param)
					{
						$pArray[strtolower($param->getName())] = null;
						$paramStr .= $param->getName();
	
						if ($param->isDefaultValueAvailable() && !isset($rArray[$param->getName()])) {
							$rArray[$param->getName()] = $param->getDefaultValue();
						}
	
						if ($i != $pCount-1) #if a parameter is missing 
						{
							$paramStr .= ", ";
						}
						
						$i++;
					}
	
					foreach ($pArray as $key => $val)
					{
						$pArray[strtolower($key)] = $rArray[strtolower($key)];
					}
	
					if (count($pArray) == $pCount && !in_array(null, $pArray))
					{
                                                #Call a callback with array of parameters with call_user_func_array
						echo call_user_func_array(array($this->serviceClass, $method), $pArray);
					}
                                        #Print an  error if there is a missing parameter
					else 
					{
						echo json_encode(array('error' => "Required parameter(s) for ". $method .": ". $paramStr));
					}
				}
				else
				{
                                        #Print an error if the  method  does not exist
					echo json_encode(array('error' => "The method " . $method . " does not exist."));
				}
			}
			else
			{
                                #Print an error if a method is not provided
				echo json_encode(array('error' => 'No method was requested.'));
			}
		}
	}

?>

