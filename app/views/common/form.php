<?php
/**
 * Expected variables:
 *   - $model: the model name as string (e.g., 'transaction')
 *   - $record: an associative array of field values (if editing). Can be empty for create.
 *   - $action: the form action URL (e.g., "/transaction/update/{$record['id']}" or "/transaction/insert")
 *   - $title: the title to show on the form (e.g., "Edit Transaction" or "Create Transaction")
 *
 * The model’s configuration is assumed to be available by instantiating the model.
 */

// Ensure the model class is loaded.
require_once __DIR__ ."/../../models/".ucfirst($model).".php";  // adjust path as needed

$modelClass = ucfirst($model);
$instance = new $modelClass();

// Get the fields configuration
$fields = $instance->tableFields;

// For foreign key fields, we might need to load the options from the foreign model.
// We will create an array to hold those extra options.
$foreignOptions = [];

// Loop through each field to check if it has a foreign key relationship.
foreach ($fields as $field => $config) {
    if (isset($config['foreign'])) {
        $foreignModel = $config['foreign']['model'];
        // Make sure to load the foreign model class. 
        require_once __DIR__ ."/../../models/".ucfirst($foreignModel).".php"; // adjust path as needed
        $foreignInstance = new $foreignModel();
        // Get all records from the foreign table.
        // You might want to customize this (ordering, filtering, etc.)
        $foreignOptions[$field] = $foreignInstance->get(); // assuming readAll() returns an array of rows.
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($title) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- TailwindCSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex justify-center items-center min-h-screen">
  <div class="max-w-lg w-full bg-white p-6 shadow-lg rounded-lg">
    <h1 class="text-2xl font-semibold mb-4 text-center"><?= htmlspecialchars($title) ?></h1>
    <form action="<?= $action ?>" method="POST" class="space-y-4">
      <?php
      // Loop through the fields configuration to build each input.
      foreach ($fields as $fieldName => $config) {
          // Skip if field is not editable (if you add such a flag, e.g., 'editable' => false)
          if (isset($config['editable']) && !$config['editable']) {
              continue;
          }

          // Get current value (if editing) or default empty string
          $value = isset($record[$fieldName]) ? htmlspecialchars($record[$fieldName]) : '';

          // Create label and determine input type.
          echo '<div class="mb-4">';
          echo '<label class="block text-gray-700 text-sm mb-1" for="' . $fieldName . '">' . $config['label'] . ':</label>';

          // If the field is to be rendered as a <select>:
          if ($config['input'] === 'select') {
              echo '<select name="' . $fieldName . '" id="' . $fieldName . '" class="w-full p-2 border border-gray-300 rounded-md" ' 
              . (isset($config['required']) && $config['required'] ? 'required' : '') . '>';
              
              // Determine the options:
              // 1. If this field has a foreign key configuration, use the foreignOptions array.
              if (isset($config['foreign'])) {
                  $options = $foreignOptions[$fieldName];
                  // Each option: the valueField and textField as defined.
                  foreach ($options as $option) {
                      $optionValue = htmlspecialchars($option[$config['foreign']['valueField']]);
                      $optionText  = htmlspecialchars($option[$config['foreign']['textField']]);
                      $selected = ($optionValue == $value) ? 'selected' : '';
                      echo "<option value=\"{$optionValue}\" {$selected}>{$optionText}</option>";
                  }
              } elseif (isset($config['options'])) {
                  // Otherwise, use options defined directly in the configuration.
                  foreach ($config['options'] as $optionValue => $optionText) {
                      $selected = ($optionValue == $value) ? 'selected' : '';
                      echo "<option value=\"" . htmlspecialchars($optionValue) . "\" {$selected}>" . htmlspecialchars($optionText) . "</option>";
                  }
              }
              echo '</select>';
          }
          // If the field is a textarea:
          elseif ($config['input'] === 'textarea') {
              echo '<textarea name="' . $fieldName . '" id="' . $fieldName . '" class="w-full p-2 border border-gray-300 rounded-md" ' . (isset($config['required']) && $config['required'] ? 'required' : '') . '>' . $value . '</textarea>';
          }
          // Otherwise, assume it’s a text input (you can add more mappings as needed).
          else {
              $inputType = $config['input']; // e.g., text, date, number
              $readOnly = (isset($config['readonly']) && $config['readonly']) ? 'readonly' : '';
              echo '<input type="' . $inputType . '" name="' . $fieldName . '" id="' . $fieldName . '" value="' . $value . '" class="w-full p-2 border border-gray-300 rounded-md" ' . (isset($config['required']) && $config['required'] ? 'required' : '') . " {$readOnly}>";
          }
          
          echo '</div>';
      }
      ?>
      <div class="flex justify-between items-center">
        <a href="<?= $GLOBALS['BASE'] . '/' . $model ?>/list" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
          Cancel
        </a>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
          <?= strpos($action, 'update') !== false ? 'Update' : 'Create' ?>
        </button>
      </div>
    </form>
  </div>
</body>
</html>
