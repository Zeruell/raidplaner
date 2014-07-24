<?php
    require_once dirname(__FILE__).'/connector.class.php';
    
    // -------------------------------------------------------------------------
    
    $gApiHelp['location'] = Array(
        'description' => 'Query value. Get a list of available locations.',
        'parameters'  => Array(
            'games' => 'Comma separated list of game ids. Only returns locations for these games. Default: empty',
            'utf8'  => 'Convert strings back to UTF8. Default: false.'
        )
    );
    
    // -------------------------------------------------------------------------
    
    function api_args_location($aRequest)
    {
        return Array(
            'games' => getParamFrom($aRequest, 'games', ''),
            'utf8'  => getParamFrom($aRequest, 'utf8', false)
        );
    }
    
    // -------------------------------------------------------------------------
    
    function api_query_location($aParameter)
    {
        $aGames = getParamFrom($aParameter, 'games', '');
        $aUTF8  = getParamFrom($aParameter, 'utf8',  false);
        
        $Parameters = Array();
        $Conditions = Array();
        
        // Filter games
        
        if ($aGames != '')
        {
            $Games = explode(',', $aGames);
            $GameOptions = Array();
            
            foreach($Games as $Game)
            {
                array_push($GameOptions, 'Game=?');
                array_push($Parameters, $Game);
            }
            
            array_push($Conditions, $GameOptions);
        }
        
        // Build where clause
        
        $WhereString = '';        
        if (count($Conditions) > 0)
        {
            foreach($Conditions as &$Part)
            {
                if (is_array($Part))
                    $Part = '('.implode(' OR ', $Part).')';
            }
            
            $WhereString = 'WHERE '.implode(' AND ',$Conditions).' ';
        }
        
        // Query
        
        $Connector = Connector::getInstance();
        $LocationQuery = $Connector->prepare('SELECT * FROM `'.RP_TABLE_PREFIX.'Location` '.$WhereString.' ORDER BY Name');
        
        foreach($Parameters as $Index => $Value)
        {
            if (is_numeric($Value))
                $LocationQuery->bindValue($Index+1, $Value, PDO::PARAM_INT);
            else
                $LocationQuery->bindValue($Index+1, $Value, PDO::PARAM_STR);
        }
        
        // Build result
        
        $Result = Array();
        $LocationQuery->loop(function($LocationRow) use (&$Result,$aUTF8) {
            array_push($Result, Array(
                'Id'     => $LocationRow['LocationId'],
                'Name'   => ($aUTF8) ? xmlToUTF8($LocationRow['Name']) : $LocationRow['Name'],
                'GameId' => $LocationRow['Game'],
                'Image'  => $LocationRow['Image'],
            ));
        });
        
        return $Result;
    }
?>
