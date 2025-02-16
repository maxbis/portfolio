<?php
/**
 * Validates a columns data structure.
 *
 * @param array $columns The columns configuration array.
 * @return array An array of error messages. Empty if no errors.
 */
function checkColumnsSyntax(array $columns): array {
    $errors = [];
    
    // Check that columns is a non-empty array.
    if (empty($columns)) {
        $errors[] = "The columns array is empty.";
        return $errors;
    }
    
    foreach ($columns as $index => $col) {
        $colLabel = "Column index $index";
        
        // Each column must be an array.
        if (!is_array($col)) {
            $errors[] = "$colLabel is not an array.";
            continue;
        }
        
        // Required keys: 'name' and 'data'
        if (!array_key_exists('name', $col)) {
            $errors[] = "$colLabel is missing the required key 'name'.";
        } elseif (!is_string($col['name'])) {
            $errors[] = "$colLabel: 'name' must be a string.";
        }
        
        if (!array_key_exists('data', $col)) {
            $errors[] = "$colLabel is missing the required key 'data'.";
        } elseif (!is_string($col['data'])) {
            $errors[] = "$colLabel: 'data' must be a string.";
        }
        
        // Optional: 'width' should be a string (like "60px").
        if (isset($col['width']) && !is_string($col['width'])) {
            $errors[] = "$colLabel: 'width' should be a string (e.g. '60px').";
        }
        
        // Optional: 'align' should be one of a known set.
        if (isset($col['align'])) {
            $validAligns = ['left', 'right', 'center'];
            if (!in_array($col['align'], $validAligns)) {
                $errors[] = "$colLabel: 'align' should be one of: " . implode(', ', $validAligns) . ".";
            }
        }
        
        // Optional: 'formatter' should be a string.
        if (isset($col['formatter']) && !is_string($col['formatter'])) {
            $errors[] = "$colLabel: 'formatter' must be a string representing a valid PHP expression.";
        }
        
        // Optional: 'aggregate'
        if (isset($col['aggregate'])) {
            if (!is_string($col['aggregate'])) {
                $errors[] = "$colLabel: 'aggregate' must be a string.";
            } else {
                $agg = $col['aggregate'];
                // If aggregate is not one of the known types, we expect it to be a formula (and thus contain curly braces).
                if ($agg !== 'sum' && $agg !== 'average') {
                    if (strpos($agg, '{') === false || strpos($agg, '}') === false) {
                        $errors[] = "$colLabel: 'aggregate' is neither 'sum' nor 'average' and does not appear to be a formula (it should contain tokens like {token}).";
                    }
                }
            }
        }
        
        // Optional: 'aggregateToken' should be a string.
        if (isset($col['aggregateToken']) && !is_string($col['aggregateToken'])) {
            $errors[] = "$colLabel: 'aggregateToken' must be a string.";
        }
        
        // Optional: 'filter' should be one of allowed values.
        if (isset($col['filter'])) {
            $validFilters = ['none', 'select', 'text'];
            if (!in_array($col['filter'], $validFilters)) {
                $errors[] = "$colLabel: 'filter' should be one of: " . implode(', ', $validFilters) . ".";
            }
        }
        
        // If the 'data' field contains tokens (i.e. a formula) we can warn if it looks unusual.
        if (isset($col['data']) && is_string($col['data'])) {
            if (strpos($col['data'], '{') !== false && strpos($col['data'], '}') === false) {
                $errors[] = "$colLabel: 'data' contains '{' but is missing a matching '}'.";
            }
            if (strpos($col['data'], '}') !== false && strpos($col['data'], '{') === false) {
                $errors[] = "$colLabel: 'data' contains '}' but is missing a matching '{'.";
            }
        }
    }
    
    return $errors;
}

function checkDataReferences(array $columns, array $data): array {
    $errors = [];
    
    // Ensure that $data is not empty.
    if (empty($data)) {
        $errors[] = "Data array is empty.";
        return $errors;
    }
    
    // Get keys from the first data record.
    $firstDataKeys = array_keys($data[0]);
    
    foreach ($columns as $colIndex => $col) {
        // Check that the 'data' key exists and is a string.
        if (!isset($col['data']) || !is_string($col['data'])) {
            $errors[] = "Column index {$colIndex}: 'data' is not set or is not a string.";
            continue;
        }
        
        $dataField = $col['data'];
        
        // Check if the data field contains any token(s) in curly braces.
        if (preg_match_all('/\{([^}]+)\}/', $dataField, $matches)) {
            // $matches[1] holds all tokens found.
            foreach ($matches[1] as $token) {
                if (!in_array($token, $firstDataKeys)) {
                    $errors[] = "Column index {$colIndex}: Token '{$token}' is not a key in the data array.";
                }
            }
        } else {
            // No tokens found, so the data field itself should be a key.
            if (!in_array($dataField, $firstDataKeys)) {
                $errors[] = "Column index {$colIndex}: Data field '{$dataField}' is not a key in the data array.";
            }
        }
    }
    
    return $errors;
}

// Validate the columns.
$errors =  checkDataReferences($columns, $data);
$errors = array_merge($errors, checkColumnsSyntax($columns));

if (!empty($errors)) {
    echo "<h2>Errors found in columns configuration:</h2>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
    }
    echo "</ul>";
    echo "<hr><pre>";
    print_r($columns);
    exit;
}
?>
