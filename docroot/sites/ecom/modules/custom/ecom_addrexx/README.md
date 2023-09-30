# Address Tool Integration with Ecom Portal

## Table of Contents

1. [Introduction](#1-introduction)
2. [Overview](#2-overview)
3. [Configuration](#3-configuration)
   3.1. [Backend Configuration](#31-backend-configuration)
   3.2. [Ecom Portal Integration](#32-drupal-commerce-integration)
4. [Autocomplete Features](#4-autocomplete-features)
   4.1. [First Name and Last Name](#41-first-name-and-last-name)
   4.2. [Zipcode](#42-zipcode)
   4.3. [City and State](#43-city-and-state)
   4.4. [County](#44-county)
   4.5. [Street Address](#45-street-address)
5. [Usage Instructions](#5-usage-instructions)
6. [Troubleshooting](#6-troubleshooting)
   6.1. [Address Not Found](#61-address-not-found)
   6.2. [API Configuration](#62-api-configuration)
   6.3. [Technical Support](#63-technical-support)
7. [Conclusion](#7-conclusion)

## 1. Introduction

Welcome to the Address Tool Integration Documentation for your Drupal-based E-commerce portal. This document provides an overview of the integration of the Addrexx Address Tool API with your website and explains how to configure, use, and troubleshoot the features related to autocomplete suggestions for addresses.

## 2. Overview

The Address Tool Integration allows your customers to easily input their address information during the checkout process by providing autocomplete suggestions for various address fields, such as first name, last name, zipcode, street address, city, state, and county. This integration relies on the Addrexx Address Tool API, which provides real-time address validation and autocomplete suggestions based on user input.

## 3. Configuration

### 3.1 Backend Configuration

To set up the Addrexx Address Tool API integration, you'll need to configure the following backend settings:

- **API Endpoint:** Enter the URL endpoint for the Addrexx Address Tool API.
- **API Secrets:** Securely store and configure API keys or secrets required for authentication with the Addrexx API.

### 3.2 Ecom Portal Integration

The integration with Ecom Portal involves mapping the API's autocomplete suggestions to specific address fields in the Commerce checkout process. Ensure that the following fields are correctly mapped:

- **First Name:** Enable autocomplete suggestions for the first name field.
- **Last Name:** Enable autocomplete suggestions for the last name field.
- **Zipcode:** Enable autocomplete suggestions for the zipcode field.
- **Street Address:** Enable autocomplete suggestions for the street address field.
- **City and State:** When the zipcode is selected from the autocomplete suggestions, it should auto-fill the city and state fields of the same address form.
- **County:** The county field should fetch autocomplete suggestions from the Addrexx API based on the city, state, and zipcode field inputs.
- **Street Address:** The street address field should fetch autocomplete suggestions from the Addrexx API based on the city and zipcode. In addition to this, there is one more api called APT which should fetch autocomplete suggestions from the 2nd field of street address based on the zipcode and street address's first field value.

## 4. Autocomplete Features

### 4.1 First Name and Last Name

When customers start typing their first name or last name, the autocomplete feature suggests options based on existing data, making it quicker and easier to complete their personal information accurately.

### 4.2 Zipcode

As customers enter their zipcode, the autocomplete feature provides suggestions for valid zipcodes, helping users select the correct option.

### 4.3 City and State

Once the user selects a zipcode, the city and state fields are automatically populated with the correct information, minimizing data entry errors. Select
field is a dropdown where city field is an autocomplete which shows autocomplete suggestion (max 20).

### 4.4 County

The county field is originally a List (text) type field added along with address field in the customer profile. It has all county data listed as options in the field configuration. On the front end, only parameterized options shown filtered by city, state and zipcode. On frontend it render as an autocomplete field and fetches autocomplete suggestions (max 20) based on the city, state, and zipcode inputs, ensuring that users can accurately select their county from a list of options.

### 4.5 Street Address

The street address field fetches autocomplete suggestions based on the city and zipcode, streamlining the process of entering detailed address information.

## 5. Usage Instructions

Here's how customers can use the autocomplete feature during the checkout process:

1. Begin typing your first name and select the suggested option.
2. Repeat the same for your last name.
3. Enter your zipcode, and choose the correct option from the suggestions.
4. Watch as the city and state fields are automatically populated.
5. Provide the city and state, and the county field will offer suggestions.
6. Finally, fill in your street address by selecting an option from the suggestions.

## 6. Troubleshooting

### 6.1 Address Not Found

If you encounter issues with address suggestions not being found, ensure that the input data, such as zipcode, city, state, and county, is accurate and matches the Addrexx API's requirements.

### 6.2 API Configuration

Double-check that the API endpoint and secrets are correctly configured in the backend settings of your website.

### 6.3 Technical Support

For technical issues, contact our technical support team for assistance.

## 7. Conclusion

With the Address Tool Integration, your customers can enjoy a seamless and efficient checkout experience by leveraging the autocomplete suggestions for address fields. This enhancement not only saves time but also ensures accurate address information, improving the overall user experience on your Drupal-based E-commerce portal.
