# VisionSphere
Camera Motion Detection for Raspberry Pi's with focus on Australian residents.

![Screenshot](https://harrishudson.com/github/visionsphere_snapshot1.svg)

## Overview
This application is a simple camera motion detection system intended to be run on Raspberry Pi micro computers. Uses could vary from security applications, to monitoring households, to wildlife recording. Technically, the application simply checks an image stream for differences in consecutive image frames to assess if motion has been detected. As it is, this application is largely intended for Australian users but could be modified for use in other countries.

## Requirements
- At least 1 **Raspberry Pi** micro computer with a Camera. Camera equipped Pi Zero's are ideal.
- A **web server** that supports serving **php** files. If your cameras will be deployed publicly, you may need a publicly accessible web hosting service - ideally with your own registered domain. cPanel hosting services are ideal. Setting up a web server that can serve php files is beyond the scope of this help documentation here. You are assumed to have access to a web server - whether that is an internal intranet web server or a public web hosting environment. 

## Build Instructions
- Create a directory that is publicly accessible on your web server.
- Recursively copy all files under this repo **server** directory to the directory on your web server.
- Using a browser, go to your web server and find the *Help and Documentation* section of this application.
- Follow the instructions under *Server Installation and Reference Guide* to complete your server configuration.
- Follow the instructions under *Client Installation and Reference Guide* to complete your Raspberry Pi build.

## Author
Harris Hudson
 
## Contributing
Pull Requests are not currently being accepted.  If you would like to request a change, or find a bug, please raise an issue.  
 
## Donate
[https://harrishudson.com/#sponsor](https://harrishudson.com/#sponsor)
