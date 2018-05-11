<?php
$url = "http://preview.data2crm.com/preview_5ae9957873c2e/service/v4_1/rest.php";
$username = "testuser";
$password = "testuser";

//function to make cURL request
function call($method, $parameters, $url)
{
  ob_start();
  $curl_request = curl_init();

  curl_setopt($curl_request, CURLOPT_URL, $url);
  curl_setopt($curl_request, CURLOPT_POST, 1);
  curl_setopt($curl_request, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
  curl_setopt($curl_request, CURLOPT_HEADER, 1);
  curl_setopt($curl_request, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($curl_request, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl_request, CURLOPT_FOLLOWLOCATION, 0);

  $jsonEncodedData = json_encode($parameters);

  $post = array(
    "method" => $method,
    "input_type" => "JSON",
    "response_type" => "JSON",
    "rest_data" => $jsonEncodedData
  );

  curl_setopt($curl_request, CURLOPT_POSTFIELDS, $post);
  $result = curl_exec($curl_request);
  curl_close($curl_request);

  $result = explode("\r\n\r\n", $result, 2);
  $response = json_decode($result[1]);
  //$response = json_decode($result);
  ob_end_flush();

  return $response;
}

function prepareParams($module, $fields, $sid)
{
  $parameters = array(

    //session id
    'session' => $sid,

    //The name of the module from which to retrieve records
    'module_name' => $module,

    //The SQL WHERE clause without the word "where".
    'query' => "",

    //The SQL ORDER BY clause without the phrase "order by".
    'order_by' => "",

    //The record offset from which to start.
    'offset' => '0',

    //Optional. A list of fields to include in the results.
    'select_fields' => $fields,

    /*
    A list of link names and the fields to be returned for each link name.
    Example: 'link_name_to_fields_array' => array(array('name' => 'email_addresses', 'value' => array('id', 'email_address', 'opt_out', 'primary_address')))
    */
    'link_name_to_fields_array' => array(
    ),

    //The maximum number of results to return.
    'max_results' => '10',

    //To exclude deleted records
    'deleted' => '0',
  );
  return $parameters;
}

function prepareData($module, $fields, $url, $sid)
{
  $get_params = prepareParams($module, $fields, $sid);
  $list_result = call('get_entry_list', $get_params, $url);

  foreach ($list_result->entry_list as $k => $obj ) {

    if ($module == 'Contacts') {

      $list[$k]['name']  = $obj->name_value_list->{$fields[1]}->value . " " . $obj->name_value_list->{$fields[2]}->value;
      $list[$k][$fields[3]] = $obj->name_value_list->{$fields[3]}->value;

    } else {

      $list[$k][$fields[1]]  = $obj->name_value_list->{$fields[1]}->value;
      $list[$k][$fields[2]] = $obj->name_value_list->{$fields[2]}->value;

    }
  }

  return $list;

}

//login -----------------------------------------
$login_parameters = array(
  "user_auth" => array(
    "user_name" => $username,
    "password" => md5($password),
    "version" => "1"
  ),
  "application_name" => "RestTest",
  "name_value_list" => array(),
);

$login_result = call("login", $login_parameters, $url);

/*
echo "<pre>";
print_r($login_result);
echo "</pre>";
*/

//get session id
$session_id = $login_result->id;


$leads_list = prepareData('Leads', array('id','name','title'), $url, $session_id);

$acc_list = prepareData('Accounts', array('id','name','industry'), $url, $session_id);

$contact_list = prepareData('Contacts', array('id', 'first_name', 'last_name', 'title'), $url, $session_id);

$task_list = prepareData('Tasks', array('id', 'name', 'status'), $url, $session_id);

$opp_list = prepareData('Opportunities', array('id', 'name', 'amount'), $url, $session_id);

$user_list = prepareData('Users', array('id', 'user_name', 'title'), $url, $session_id);

?>


<!DOCTYPE html>
<!--[if IE 7]><html class="no-js ie7 oldie" lang="en-US"> <![endif]-->
<!--[if IE 8]><html class="no-js ie8 oldie" lang="en-US"> <![endif]-->
<html lang="en">
<head>
    <meta charset="utf-8">

    <!-- TITLE OF SITE-->
    <title> List of Modules </title>

    <!-- META TAG -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="List of Modules">

</head>
<body>
    <ul class="styles"> Leads: <?php foreach ($leads_list as $lead) {
        echo  " <li> {$lead['name']} ||  {$lead['title']}</li>  ";
      } ?>

    </ul>
    <ul class="styles"> Accounts: <?php foreach ($acc_list as $acc) {
        echo  " <li> {$acc['name']} ||  {$acc['industry']}</li>  ";
      } ?>

    </ul>
    <ul class="styles"> Contacts: <?php foreach ($contact_list as $contact) {
        echo  " <li> {$contact['name']} ||  {$contact['title']}</li>  ";
      } ?>

    </ul>
    <ul class="styles"> Tasks: <?php foreach ($task_list as $task) {
        echo  " <li> {$task['name']} ||  {$task['status']}</li>  ";
      } ?>

    </ul>
    <ul class="styles"> Opportunities: <?php foreach ($opp_list as $opp) {
        echo  " <li> {$opp['name']} ||  {$opp['amount']}</li>  ";
      } ?>

    </ul>
    <ul class="styles"> Users: <?php foreach ($user_list as $user) {
        echo  " <li> {$user['user_name']} ||  {$user['title']}</li>  ";
      } ?>

    </ul>
</body>
</html>


