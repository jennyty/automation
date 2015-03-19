#usr/bin/python
import getopt
import argparse
import sys
import socket
import random
import thread
import time
import os
import re
import MySQLdb.cursors
from datetime import datetime
import requests

def main(argv):

   usage()

   global hostaddress    
   hostaddress = '105.145.40.34'   
   global devicelist
   global testcaselist
   global testcaserunid
   global gResultString
   global gSearchString
   global socket1,socket2
   global gBackgroundThread
   
   gSearchString = "(Json\(.*.*\))"
   gBackgroundThread = True
   parser = argparse.ArgumentParser()
   parser.add_argument('-device', nargs='*')
   parser.add_argument('-testcase', nargs='*')

   args = parser.parse_args()
   print "Argument namespace is: ", args
   testcaserunid = 0
   devicelist = args.device
   print "List of device is: ", devicelist
   testcaselist = args.testcase
   print "List of testcase is: ", testcaselist
   userId = 0

   process(testcaselist, devicelist)


def usage():
   print "USAGE:" 
   print "**************************************************************"
   sys.stderr.write  (
    "Usage: python testcase.py -device xxxx -testcase nnn\n"
    "   Or: python testcase.py -device xxxx yyyy -testcase nnn mmm\n"
   )
   print "**************************************************************"
   print "\n"


def process(testcaselist, devicelist):
  global socket1,socket2
  for testcase in testcaselist:
   socket1 = doConnection(devicelist[0])
   socket2 = 0
   if (len(devicelist) > 1):
     if (devicelist[0] == devicelist[1]):
       socket2 = socket1
     else:
       socket2 = doConnection(devicelist[1])
   try:
     print "thread1"
     thread.start_new_thread( doThread, (0,devicelist, testcase, socket1) )
     if (len(devicelist) > 1 and devicelist[0] != devicelist[1]):
       print "thread2"
       thread.start_new_thread( doThread, (0,devicelist, testcase, socket2) )
   except:
    print "Error: unable to start thread"
   run(testcase,devicelist,socket1,socket2)
   while 1:
     pass

def run(tc,devicelist,socket1,socket2):
   print "Starting test case: ", tc 
   port ='6000'


   if (tc == '0' or tc == 'test'):  
      doTest(socket1)
   if (tc == '1' or tc == 'milkvideo-openclose'):  
      print "calling doOpenCLose"
      doOpenCloseTest(socket1,"Milk Video", "com.samsung.milk.milkvideo")
   if (tc == '2' or tc == 'milkvideo-launch'):  
      doLaunchTest(socket1,"Milk Video", "com.samsung.milk.milkvideo")
   if (tc == '3' or tc == 'milkvideo-power'):  
      doPowerConsumption(socket1,"Milk Video", "com.samsung.milk.milkvideo")
   if (tc == '4' or tc == 'milkvideo-tracking'):  
      doMemoryTrack(socket1,"Milk Video", "com.samsung.milk.milkvideo")
   if (tc == '5' or tc == 'following'):  
      doFollowFeatureTest(socket1,socket2)
   if (tc == '6' or tc == 'milkmusic-openclose'):  
      doOpenCloseTest(socket1, "Milk", "com.samsung.mdl.radio")
   if (tc == '7' or tc == 'milkmusic-launch'):  
      doLaunchTest(socket1, "Milk", "com.samsung.mdl.radio")
   if (tc == '8' or tc == 'milkmusic-power'):  
      doPowerConsumption(socket1, "Milk", "com.samsung.mdl.radio")
   if (tc == '9' or tc == 'milkmusic-tracking'):  
      doMemoryTrack(socket1, "Milk", "com.samsung.mdl.radio")

   print "Ending TESTCASE: ", tc + "\n"
   sys.exit(0)


def readlines(sock, recv_buffer=4096, delim='\n'):
        buffer = ''
        data = True
        while data:
                data = sock.recv(recv_buffer)
                buffer += data

                while buffer.find(delim) != -1:
                        line, buffer = buffer.split('\n', 1)
                        yield line
        return


def doConnection(host): 
   client_socket = socket.socket(socket.AF_INET, socket.SOCK_STREAM) 
   print "In DO CONNECTION func"
   client_socket.connect((host,6000)) 
   return client_socket
  

def doJson(l):
   ts = time.time()
   rn=(random.randint(0,123456))
   randomNumber =   "%d_%05d" %  (ts,rn)

   hash = {} 
   print "l:",l
   for i in range(0,len(l),2): 
      hash[l[i]] = l[i+1]
   hash['uid'] = randomNumber 
   
   json = 'Json({' 
   for key in hash:
     json = json + '"' + key + '":"' + hash[key] + '",'
   json = json[0:len(json)-1] + '})\n'
   print "My json: " , json
   return json
   return textName


def expect():
  global gResultString
   
  exitFlag = 0
  returnValue = None
  startTime = time.time()
  
  gResultString = '' 
  
  while (exitFlag == 0):
    if (gResultString != ''):
      print "gResultString=",gResultString
      exitFlag = 1 
      returnValue = gResultString
    else:
      #print "sleep..."
      time.sleep(0.1)
    if (time.time() > startTime + 30):
      print "time: " , time.time()
      print "startTime: ", startTime
      exitFlag = 1 
  
  return returnValue

  

def log(l):
  print "In log function"
  json= doJson(l)
  print "log: json=",json
  return json
  
def pressHome(l):
  print "In pressHome function"
  json=doJson(l)
  return json

 
def pressBack(l):
  print "In pressBack function"
  json=doJson(l)
  return json


def launchApp(l):
  print "In launchApp function"
  json=doJson(l)
  return json

def click(l):
  print "In click function"
  json=doJson(l)
  return json

def swipe(l):
  print "In swipe function"
  json=doJson(l)
  return json

def getText(l):
  print "In getText function"
  json=doJson(l)
  return json

def setText(l):
  print "In setText function"
  json=doJson(l)
  return json

def exists(l):
  print "In exists function"
  json=doJson(l)
  return json


def getProcessId(l):
  print "In getProcessId function"
  json=doJson(l)
  return json

def getDumpSysMemInfo(l):
  print "In getDumpSysMemInfo function"
  json=doJson(l)
  print "GetDump: json=",json
  return json

def getDeviceInfo(l):
  print "In getDeviceInfo function"
  json=doJson(l)
  return json

def killApp(l):
  print "In getDeviceInfo function"
  json=doJson(l)
  return json

def regexFind(regexStr,line):
  matchObj = re.search(regexStr,line)
  if matchObj:
    return True
  return False

def doThread(userId,host,testcaseId,device):
   global gResultString
   global gSearchString
   global gBackgroundThread
   stamp =  "%d" % time.time()

   while gBackgroundThread:
    print "In doThread function"
    base =  "/tmp/AutomatedScriptLogs"
    path =  base  + "/" + ("%06d" % userId)
    file = "log_" + str(stamp) + "_" + str(host[0]) + "_" + str(testcaseId) + ".log"
    print file
    os.system("mkdir -p " + path)
    print "path= " ,path
    fd = open(path + "/" + file, 'w')
    os.system("chmod -R 777 " + base)
    for line in readlines(device):
      if (gBackgroundThread == False):
        sys.exit(0)
      fd.write(file)
      #print "doThread line=",line
      if line == "":
        cnt = cnt + 1
        if cnt > 10:
          print "ERROR, more than 10 readlines failures"
          gExitFlag = 1
      elif regexFind(gSearchString,line):
         print 'FOUND:' + line
         gResultString = line
         gExpectString = ''


def getTestCaseRunId():
   global testcaserunid
   if testcaserunid == 0:
     testcaserunid = time.time()
   return testcaserunid

def callSend(socketX, regexStr, jlst):
   global gSearchString
   time.sleep(5)
   gSearchString = regexStr
   socketX.send(jlst + "\n")
   resp = expect()
   return resp
  
def doLaunchTest(socket1,appText,processName):
   print "doLaunchTest..."

   callSend(socket1, "(Json\(.*.*\))", log(["header","Starting Launch Test","function","log"]))

   callSend(socket1, "(Json\(.*.*\))", killApp(["processName",processName,"function","killApp"]))

   for num in range(1,100):
     callSend(socket1, "(Json\(.*.*\))", log(["function","log","text","Iteration "+str(num)]))
     callSend(socket1, "(Json\(.*.*\))", launchApp(["header","Starting Launch Test","text",appText,"function","launchApp"]))
     resp = callSend(socket1, "(Json\(.*.*\))", getDumpSysMemInfo(["processName",processName,"function","getDumpSysMemInfo"]))
     extractAndInsert(getTestCaseRunId(), resp)
     callSend(socket1, "(Json\(.*.*\))", killApp(["processName",processName,"function","killApp"]))
   socket1.send(log(["header","Ending Launch Test","function","log"])+"\n")

def doOpenCloseTest(socket1,appText,processName):
   print "doOPenclosetest..."
   time.sleep(5)
   callSend(socket1, "(Json\(.*.*\))", log(["header","Starting Open/Close Test","function","log"]))
   callSend(socket1, "(Json\(.*.*\))", killApp(["processName",processName,"function","killApp"]))
   for num in range (1,200):
     callSend(socket1, "(Json\(.*.*\))", log(["function","log","text","Iteration "+str(num)]))
     callSend(socket1, "(Json\(.*.*\))", launchApp(["header","Starting Open/Close Test","text",appText,"function","launchApp"]))
     resp = callSend(socket1, "(Json\(.*.*\))", getDumpSysMemInfo(["processName",processName,"function","getDumpSysMemInfo"]))
     extractAndInsert(getTestCaseRunId(),resp)

     callSend(socket1, "(Json\(.*.*\))", pressBack(["function","pressBack"]))
     if (num == 100):
       sleepTime = 1800
       callSend(socket1, "(Json\(.*.*\))", log(["header","Sleeping for "+ sleepTime+ " seconds","function","log"]))
       time.sleep(sleepTime)
   callSend(socket1, "(Json\(.*.*\))", log(["header","Ending Open/Close Test","function","log"]))

def doTest(l):
   callSend(socket1, "(Json\(.*.*\))", getDeviceInfo(["header","Starting doTest Test","function","getDeviceInfo"]))
   doOpenCloseTest(socket1,"YouTube","com.google.android.youtube")
   
def doMemoryTrack(socket1,appText,processName):
   global gBackgroundThread
   print " In doMemoryTrack function"
   callSend(socket1, "(Json\(.*.*\))", log(["header","Starting Memory Track Test","function","log"]))
   callSend(socket1, "(Json\(.*.*\))", killApp(["processName",processName,"function","killApp"]))
   callSend(socket1, "(Json\(.*.*\))", launchApp(["text",appText,"function","launchApp"]))
   resp = callSend(socket1, "(Json\(.*.*\))", getDumpSysMemInfo(["processName",processName,"function","getDumpSysMemInfo"]))
  
   for num in range(1,10000):
     extractAndInsert(getTestCaseRunId(), resp)
     time.sleep(5)
   resp=callSend(socket1, "(Json\(.*.*\))", log(["header","Ending Memory Track Test","function","log"]))
   gBackgroundThread = False
   print "before exit"
   time.sleep(5)
     

def extractAndInsert(testCaseRunId,memLogLine): 

   print "In extractAndInsert function ..."

   date=datetime.now()
   mystring=('%0s-%02s-%02s %02s:%02s:%02s'%(date.year,date.month,date.day,date.hour,date.minute,date.second))

   dalvikHeapSize =0
   dalvikHeapAlloc =0
   matchObj1 = re.search( r',"memory1":"[ ]+[A-Za-z]*[ ]+Heap[ ]+([0-9]+)[ ]+([0-9]+)[ ]+([0-9]+)[ ]+([0-9]+)[ ]+([0-9]+)[ ]+([0-9]+)[ ]+([0-9]+)', memLogLine, re.M|re.I)
   if matchObj1:
      nativeHeapSize =  matchObj1.group(5)
      nativeHeapAlloc = matchObj1.group(6)
      print "Native Heap Size", nativeHeapSize
      print "Native Heap Alloc", nativeHeapAlloc
      if (dalvikHeapSize != 0 and dalvikHeapAlloc != 0):
       try:
         insertMemoryValues(testCaseRunId, date, nativeHeapSize, nativeHeapAlloc, dalvikHeapSize, dalvikHeapAlloc)
       except:
          print "***Error: unable to insert !!!"
   
   matchObj2 = re.search( r',"memory2":"[ ]+[A-Za-z]*[ ]+Heap[ ]+([0-9]+)[ ]+([0-9]+)[ ]+([0-9]+)[ ]+([0-9]+)[ ]+([0-9]+)[ ]+([0-9]+)[ ]+([0-9]+)', memLogLine, re.M|re.I)
   if matchObj2:
      dalvikHeapSize =  matchObj2.group(5)
      dalvikHeapAlloc = matchObj2.group(6)
      print "Dalvik Heap Size", dalvikHeapSize
      print "Dalvik Heap Alloc", dalvikHeapAlloc
      try:
        insertMemoryValues(testCaseRunId, date, nativeHeapSize, nativeHeapAlloc, dalvikHeapSize, dalvikHeapAlloc)
      except:
        print "***Error: unable to insert !!!"

def doFollowFeatureTest(device_1,device_2):
   global gBackgroundThread
   milkVideo = "com.samsung.milk.milkvideo"
   callSend(socket1, "(Json\(.*.*\))", launchApp(["text","Milk Video","function","launchApp"]))
   resp = callSend(socket1, "(Json\(.*.*\))", getText(["resource","com.samsung.milk.milkvideo:id/title","instance","0","function","getText"]))
   time.sleep(2)
   mObj = re.search( r',"text":[^"]*"([^"]+)"', resp, re.M|re.I)
   if mObj:
     title = mObj.group(1)
     print "title: ", title
   beginVideo = title
   print "beginVideo: " , beginVideo
  
   print "--- Start Video: " 
   if (device_2 != 0):
     callSend(socket2, "(Json\(.*.*\))", launchApp(["text","Milk Video","function","launchApp"]))
     callSend(socket2, "(Json\(.*.*\))", swipe(["direction","up","count","2","function","swipe"]))
     time.sleep(1)
     resp = callSend(socket2, "(Json\(.*.*\))", getText(["resource","com.samsung.milk.milkvideo:id/title","instance","2","function","getText"]))
     matchObj = re.search( r',"text":[^"]*"([^"]+)"', resp, re.M|re.I)
     if matchObj:
       title = matchObj.group(1)
       print "title: ", title

       repostedVideo = title
       print "--- Reposted Video: ", repostedVideo
   
     #result=''
     cnt = 0
     while (re.search ("success",resp) != 1):
       cnt = cnt + 1
       if (cnt >3):
         print "---> ABORTED: Could not repost\n"
         return 0
       resp = callSend(socket2, "(Json\(.*.*\))", click(["resource","com.samsung.milk.milkvideo:id/title","instance","2","duration","36","direction","none","function","click"]))
     matchObj = re.search( r',"return":[^"]*"([^"]+)"', resp, re.M|re.I)
     if matchObj:
       result = matchObj.group(1)
       print "result: ", result
    
   time.sleep(2)
   callSend(socket1, "(Json\(.*.*\))", launchApp(["text","Milk Video","function","launchApp"]))
   resp = callSend(socket1, "(Json\(.*.*\))", getText(["resource","com.samsung.milk.milkvideo:id/title","instance","0","function","getText"]))
   endVideo= title
   print "--- End Video: ", endVideo
   #if (repostedVideo == endVideo):
   if (endVideo != ''):
      print "---> PASSED"
   else:
      print "---> FAILED"
   gBackgroundThread = False
   print "before exit"
   time.sleep(5)

def insertMeasurementValue(run,date,value,datatype):
   print "In insertMeasurementValue function..."
   payload = (('run',run),('date',date),('value',value),('type',datatype))
   r = requests.get("http://" + hostaddress + "/dev/dashboard/request.php?method=insertMeasurementValue", params=payload);


### The below function will pass parameters to REST server ###
def insertMemoryValues(run, date, nativeHeapSize, nativeHeapAlloc, dalvikHeapSize, dalvikHeapAlloc):
  try:
    print "In insertMemoryValues"
    print "hostaddress: ", hostaddress
    payload = (('run',run),('date',date),('nativeHeapSize',nativeHeapSize),('nativeHeapAlloc',nativeHeapAlloc),('dalvikHeapSize',dalvikHeapSize),('dalvikHeapAlloc',dalvikHeapAlloc))
    print "payload=",payload
    r = requests.get("http://" + hostaddress + "/dev/dashboard/request.php?method=insertMemory", params=payload);
    print (r.url)
    print "response value: ", r
  except:
    print "***Error: unable to insert to DB!!!"


def doPowerConsumption(socket1,appText,processName):
   global gSearchString
   global gBackgroundThread 
   print "doPowerConsumption..."
   callSend(socket1, "(Json\(.*.*\))", log(["header","Starting Power Consumption Test","function","log"]))
   callSend(socket1, "(Json\(.*.*\))", killApp(["processName",processName,"function","killApp"]))
   callSend(socket1, "(Json\(.*.*\))", launchApp(["header","Starting Launch Test","text",appText,"function","launchApp"]))

   for num in range (1,100):
     print "dopower num=",num
     resp = callSend(socket1, "(Json\(.*.*\))", log(["function","log","text","Iteration "+str(num)]))
     gSearchString = "(.*BatteryService.*level:.*)"

     matchObj = re.search("level:(\d+)",resp)
     if matchObj:
       print "Match match"
       callSend(socket1, "(.*BatteryService.*level:.*)", log(["function","log","header", result ]))
   gBackgroundThread = False
   print "before exit"
   time.sleep(5)


#######################################
#
# Program starts here
#
#######################################
if __name__ == "__main__":
    main(sys.argv[1:])
   


