# Ecom Stock Mangement.

## 1. Introduction

Welcome to the Stock Management Documentation for your Drupal-based E-commerce portal. This document provides an overview of the stock management for the Ecom site.

## 2. Overview

This module allow us to get the stock value of each products from the file shared in FTP server from the SAP using a cron job, Decreases the stock value after purchase and check the stock before the purchase.

## 3. Configuration

### 3.1 FTP server configuration

We have a config form to hold following details for connecting to FTP server.

- **SFTP Host Name:** Host Name of the FTP server.
- **SFTP User Name:** FTP server Username. This is password field for security purpose.
- **SFTP Password:** FTP server password.
- **SFTP Port Number:** Default port is 22.
- **SFTP Root Folder:** The folder name of FTP server where the files for inventpry update exist.


### 3.2 Product variation stock field.

We have added a interger field called "stock". Product's In stock/Out status is based on this field.
if (stock >= minimum ordered quantity of a product) then it is in stock else it is out of stock.

### 3.3 Cron job configuration.

We have build a drush command(ecom:inventory-update) to update the above stock field from the FTP Server.We need to configure it on Acquia as schedule job.

## 4. Automatic stock update from FTP files.

We have a drush command which run in every 24 hrs.
It will take all the files from the server to local public folder. And then It will update the Stock field value of the products mentioned in the FTP files. After that it will delete those FTP files from the local public folder as well as from the FTP folder.

## 4.1. Automatic stock decrease after product purchase.

Stock value will be decreased after the purchasing products by its purchased quantity. It is done by subscribing "commerce_order.place.post_transition" event in UpdateStockEventSubscriber.php

## 4.1. Stock validity check.

We check the stock validity using VariationAvailabilityChecker.php file so that customer cannot purchase the out of stock product.

### 5 Sending mails on Failure.

We are sending mails for the following situations:

1. If FTP Connection fails.
2. If FTP server file contains no data.


## 6. Conclusion

This module is resonsible for below tasks:

1. Updating stock using drush command from ftp server.
2. Decreasing stock value after purchasing of product.
3. Validating stock value before purchasing of the product in cart page.

