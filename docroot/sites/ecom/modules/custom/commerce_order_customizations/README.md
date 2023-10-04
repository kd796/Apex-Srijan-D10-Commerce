# Ecom Order Mangement.

## 1. Introduction

Welcome to the Order Management Documentation for your Drupal-based E-commerce portal. This document provides an overview of the order creation and update process.

## 2. Overview

It has two parts:
1. Order Creation.
2. Order Update.

Order creation- We have a cron job which runs in every 15 mint. It takes all the order in that interval and creates a XML file and push it to a FTP folder. Note: each XML file can contain multiple orders.

Order Update- We have a cron job which runs in every 15 mint. It takes the xml file from FTP server and update the orders in Drupal.

## 3. Configuration

### 3.1 FTP server configuration

We have a config form to hold following details for connecting to FTP server.

- **SFTP Host Name:** Host Name of the FTP server.
- **SFTP User Name:** FTP server Username. This is password field for security purpose.
- **SFTP Password:** FTP server password.
- **SFTP Port Number:** Default port is 22.
- **SFTP Root Folder:** The folder name of FTP server.


### 4 Order creation.

Our drush command is creating a XML file which contains the orders if they satisfy below conditions:
   a.Orders are not processed earlier.
   b.Payment status of the order is completed.
   c.Order state is processing.
We have added one boolean field which is basically responsible for idicaticating whether the order is previously processed or not. After exporting the order, we are making this field's value true.

We have created our custom workflow in 'commerce_order_customizations.workflows.yml'.

### 4 Order update.

Our drush command taking XML files from FTP server and creating shipment out of it.
Note: Shipment created through Drush command should not be edited manually.
Changing the status of the order to completed if ordered item quantity is equals to total shipped quantity which we are getting from FTP server file.
Each XML file contains a single order.After completion of update, it deletes the file from FTP folder.

### 5 Sending mails on Failure.

Order Update:

a.Sending mail if a remote file has no Data.
b.Sending mail if the Local folder for order update does not exist.
c.Sending mail if FTP Connection fails.
d.Sending mail if There were errors to parse the XML file.

Order Creation:

a.Sending mail if FTP Connection fails.
b.Sending mail if unable to export to FTP server.

## 6. Conclusion

This module is resonsible for below tasks:

1. Creation of order export xml file.
2. Update the order from xml file by creating shipment programatically.

