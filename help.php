<html><head><title></title>


</head><body>

<div style='text-align:left'><b>Loading Agent on the device</b><br /><hr>
<ul><li>Download the jar to the local system <a href='../uploads/uiautomator.jar'>Agent Download</a></li>
<li>Copy the jar to the device: <span style='color:red'>adb push uiautomator.jar /data/local/tmp/</span></li>
<li>Run the script on the device: <span style='color:red'>adb shell uiautomator runtest uiautomator.jar -c uiautomator.Agent --nohup</span></li>
<li>Confirm you can connect to the device</li>
</ul></div>

</body></html>
