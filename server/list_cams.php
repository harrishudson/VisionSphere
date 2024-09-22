<?php 

 include 'common.php';

 if (! active_session()) {
  $return_url = $_SERVER['PHP_SELF'];
  $return_url_hex = urlencode($return_url);
  header('Location: login.php?RETURN_URL='.$return_url_hex);
  exit();
 }; 

 page_top();

 echo <<<EOF
 <h2>List Cams</h2>
EOF;

 $cams = get_cams();
 $s = 0;
 if ($cams) {
  $s = sizeof($cams);
 }; 
 if ($s > 0) {
  echo <<<EOF
 <p>
 <table class="tbl">
 <thead>
  <tr>
   <th>Name</th>
   <th>Noise Threshold</th>
   <th>Pending Camera Action</th>
   <th>Pending System Action</th>
   <th>BOM Station ID</th>
   <th>BOM Station WMO</th>
   <th>Wind Stop Km/h</th>
   <th>Last Config Poll</th>
   <th>Edit</th>
   <th>Delete</th>
  </tr>
 </thead>
 <tbody>
EOF;

 foreach($cams as $cam) {
  $f0 = escHTML($cam[0]);
  $f1 = escHTML($cam[1]);
  $f2 = escHTML($cam[2]);
  $f3 = escHTML($cam[3]);
  $f4 = escHTML($cam[4]);
  $f5 = escHTML($cam[5]);
  $f6 = escHTML($cam[6]);
  $f7 = escHTML($cam[7]);

  echo <<<EOF
  <tr>
   <td>{$f0}</td>
   <td>{$f1}</td>
   <td>{$f2}</td>
   <td>{$f3}</td>
   <td>{$f4}</td>
   <td>{$f5}</td>
   <td>{$f6}</td>
   <td>{$f7}</td>
   <td>
    <form action="edit_cam.php" method="POST">
     <input type="hidden" name="CAM_LABEL" value="${f0}">
     <input type="submit" value="Edit">
    </form>
   </td>
   <td>
    <form action="delete_cam.php" method="POST">
     <input type="hidden" name="CAM_LABEL" value="${f0}">
     <input type="submit" value="Delete">
    </form>
   </td>
  </tr>
EOF;
  };
 echo <<<EOF
  </tbody>
 </table>
 </p>
EOF;
 };
 if ($s == 0) {
  echo "<p>No Cam's defined</p>";
 };

 page_bottom();
?>
