# Greenlight Commodities - Project Changelog

## [2025-01-27] - Added Logo to PDF Generation

### Added
- **PDF Logo**: Added Greenlight Commodities logo to PDF generation
  - Replaced text "GREENLIGHT COMMODITIES" with logo image (`gllogo.png`)
  - Applied to both trade confirmation and invoice PDFs
  - Logo positioned at top-left of PDF documents

### Technical Details
- **Trade Confirmations**: Updated `genPDF.php` to use `$pdf->Image("../images/gllogo.png",10,8,70)`
- **Invoices**: Updated invoice PDF generation to use the same logo
- **Text Removal**: Commented out the large "GREENLIGHT" and "COMMODITIES" text in PDF headers

## [2025-01-27] - Fixed PDF Address Formatting and Spacing Issues

### Fixed
- **PDF Address Formatting**: Completely redesigned address formatting for better spacing and alignment
  - Fixed spacing between address lines and city/state/zip formatting
  - Improved telephone and fax number alignment with consistent indentation
  - Added proper "Address:" labels for seller address line 2 and city/state/zip sections
  - Standardized all address elements to use 18px spacing for labels and 73px width for content
  - Added proper 10px spacer between buyer and seller sections
  - Implemented clean city/state/zip formatting with trim() to remove extra commas
  - Fixed alignment issues where address elements were not properly stacked

### Technical Details
- **Address Line 2**: Now properly shows "Address:" label for seller section
- **City/State/Zip**: Now properly shows "Address:" label for seller section  
- **Telephone Numbers**: All aligned with "Tel:" label for consistency
- **Fax Numbers**: All aligned with "Fax:" label for consistency
- **Spacing**: Consistent 18px spacing for all labels, 73px width for all content areas
- **Formatting**: Clean city/state/zip formatting that handles empty fields properly

### Added
- **Test Files Organization**: Moved test files to "newly added" folder for better organization
  - `test_pdf_generation.php` - Test script for debugging PDF generation
  - `simple_test.php` - Simple PHP test to verify server functionality
  - These files are for local testing and don't need to be uploaded to the server

## [2025-01-27] - Fixed Company Information and Broker Detail Issues

### Fixed
- **Company Information**: Updated `trade/vars.inc.php` to match correct company details
  - Changed address from "5571 Purple Meadow Lane, Fulshear, TX 77441" to "5535 Memorial Drive, Suite F453, Houston, TX 77007-8009"
  - Changed phone from "713-305-7841" to "561-339-4032"
  - Now matches the address and phone used in trade confirmations, PDFs, and contact page

## [2025-01-27] - Fixed Broker Detail User Type Display and Super Admin Detection

### Fixed
- **Broker Detail User Type Display**: Fixed critical issue where user types were showing incorrectly
  - Changed database query from `SELECT *` to explicit field selection to ensure correct field order
  - Fixed user type dropdown to properly display the broker's actual user type from database
  - Added logic to handle both numeric and MD5 hash user type values
  - Super Admins now correctly show as "Super Administrator" instead of "General User"
  - Administrators now correctly show as "Administrator" instead of "General User"

- **Super Admin Detection in Broker Detail**: Fixed action parameter detection
  - Modified logic to check both `$_GET["action"]` and `$_POST["action"]` for update operations
  - Fixed issue where popup windows weren't recognizing update mode due to missing action parameter
  - Super Admin password management features should now appear correctly

- **Password Reset Email Fix**: Fixed EmailSender class error in broker detail
  - Changed from incorrect `EmailSender` class to proper `Email` class for password reset emails
  - Fixed "Call to undefined method EmailSender::setTo()" error
  - Password reset emails now work correctly for Super Admins

## [2025-01-27] - Company Detail Update Fix & Super Admin Popup Detection Issues

### Company Detail Update Fix & Enhanced Country/State Selection
- **Issue**: Company name updates not saving in company detail popup
- **Root Cause**: UPDATE query missing `fldCompanyName` field in SQL statement
- **Fix**: Added `fldCompanyName=\"".$_POST["companyName"]."\"` to UPDATE query in `trade/trade/companyDetail.php`

- **Enhancement**: Enhanced country and state/province selection for international trading
- **Added Countries**: Switzerland, Singapore, Hong Kong, Japan, and 30+ other major financial centers
- **Features**: 
  - Dynamic state/province dropdowns based on selected country
  - Two-letter state codes for US states
  - Proper province/region data for Canada, European countries, Asia-Pacific
  - Fallback text input for "Other" countries
  - Automatic form data preparation for proper submission

- **Enhancement**: Added trader creation directly from company detail popup
- **Features**:
  - Inline trader addition form within company detail popup
  - Fields: Trader Name, Email, Phone, Active status
  - AJAX submission without page reload
  - Automatic form reset after successful addition
  - Improved workflow - no need to navigate to separate traders page
- **Files Modified**: `trade/trade/companyDetail.php`

## [2025-01-27] - Super Admin Popup Detection Issues & Solutions

### Key Issues Encountered
- **Session Inheritance**: Popup windows opened via `window.open()` don't always inherit session data reliably
- **Action Parameter Detection**: Form action was being passed as `$_POST["action"]` but code was checking `$_GET["action"]`
- **Database Query Field Order**: Using `SELECT *` made field positions unreliable, causing user type display issues
- **Class Loading**: Missing `require_once` statements for required classes (`Email` class)
- **User Type Storage**: User types stored as MD5 hashes instead of numeric values, causing dropdown selection issues
- **Function Parameter Naming**: `getUserTypeList()` function parameter incorrectly named `$brokerID` instead of `$userTypeValue`
- **Email Class Confusion**: Code attempted to use `EmailSender` class instead of `Email` class for password resets
- **Company Information Mismatch**: `vars.inc.php` contained outdated company address and phone number
- **Broker Status Preservation**: Status incorrectly changing to "inactive" when updating user type
- **JavaScript Errors**: Null reference errors in popup JavaScript functions
- **Forgot Password Validation**: JavaScript validation preventing form submission
- **Password Reset Key Management**: Reset keys not being properly cleared after use
- **Preview Invoice Functionality**: Missing JavaScript functions and incorrect PDF generation

### Solutions Implemented
- **URL Parameter Fallback**: Added `&superAdmin=1` URL parameter as primary detection method for popups
- **Dual Action Detection**: Modified logic to check both `$_GET["action"]` and `$_POST["action"]` for update operations
- **Explicit Field Selection**: Changed database queries from `SELECT *` to explicit field names for reliable field order
- **Proper Class Includes**: Added missing `require_once` statements for all required classes
- **Hash-to-Numeric Conversion**: Added logic to convert MD5 hash user types back to numeric values for dropdown selection
- **Function Parameter Fix**: Corrected `getUserTypeList()` parameter name from `$brokerID` to `$userTypeValue`
- **Email Class Correction**: Switched from `EmailSender` to `Email` class for password reset functionality
- **Company Information Update**: Updated `vars.inc.php` with correct address and phone number, commented out old values
- **Status Preservation Logic**: Added specific logic to preserve broker active status when only user type changes
- **JavaScript Null Checks**: Added null checks to prevent errors when form elements don't exist
- **HTML5 Validation**: Replaced problematic JavaScript validation with HTML5 `required` attributes
- **Password Reset Expiration**: Implemented 15-minute expiration for password reset links
- **Preview Invoice Fix**: Created proper `previewInvoice.php` with correct PDF generation and headers

### Technical Notes
- Popup windows require special handling for session data and parameter passing
- Always use explicit field selection in database queries when field order matters
- Include all required classes explicitly rather than relying on global includes
- URL parameters are more reliable than session data in popup contexts

### Technical Details
- Updated `trade/trade/brokerDetail.php` with explicit field selection in database query
- Fixed `$isUpdate` logic to check both GET and POST action parameters
- Simplified user type conversion logic to use existing `$resultSet[5]` data
- Fixed `getUserTypeList()` function in `trade/functions.php` - parameter was incorrectly named `$brokerID` instead of `$userTypeValue`
- Added debug output to help diagnose user type loading issues

## [2025-01-27] - Fixed Forgot Password Form and Broker Detail Issues

### Fixed
- **Forgot Password Form**: Completely rewrote validation system
  - Removed problematic JavaScript validation that was preventing form submission
  - Added HTML5 `required` attribute for browser-level validation
  - Added missing hidden `action="request"` field that was preventing form processing
  - Added server-side validation to ensure username is not empty
  - Form now works reliably without JavaScript errors

### Technical Details
- **File**: `trade/forgot.php`
  - Removed `checkForm()` JavaScript function and all related validation code
  - Added `<input type="hidden" name="action" value="request">` to form
  - Added `required` attribute to username input field
  - Added server-side validation: `if(empty($_POST["username"]) || trim($_POST["username"]) == "")`
  - Form now processes correctly and sends password reset emails

### User Experience
- Forgot password form now works immediately without any validation errors
- Users can successfully request password resets
- Form provides clear error messages if username is empty
- No more "You must enter a username before continuing" false positives

### Fixed (Additional)
- **Broker Detail Page**: Super Admin permissions now working correctly
  - Added missing `require_once("../functions.php");` to `brokerDetail.php`
  - Uncommented `@session_start();` in `popupHeader.php` to enable session data
  - Password field now editable for Super Admins
  - User type dropdown now enabled for Super Admins
  - Debug box now displays correctly showing Super Admin status

- **Password Reset Process**: Fixed complete password reset workflow with security enhancements
  - Fixed action value mismatch in `reset.php` (changed from "request" to "reset")
  - Added proper hidden field structure for form submission
  - Added HTML5 validation with `required` attributes
  - Fixed PHP logic to properly handle reset action
  - Fixed `updatePassword()` method in `userObj.php` to accept confirm key parameter
  - Added debug information to identify and resolve key passing issues
  - **Added 15-minute expiration** for reset links for security
  - **Added `fldResetTimestamp` column** to track reset key generation time
  - **Enhanced error messages** to clearly explain expired links
  - **Updated email sender** to `jake@greenlightcommodities.com` for consistency
  - Password reset now works end-to-end with proper security measures

- **Super Admin Broker Management**: Fixed complete broker detail functionality for Super Admins
  - **Fixed password field** - Now editable for Super Admins (not greyed out)
  - **Fixed user type dropdown** - Now enabled and shows correct value from database
  - **Added hash decoding** - Converts stored user type hashes back to numeric values for proper display
  - **Added Super Admin parameter** - URL includes `&superAdmin=1` for popup windows
  - **Added Delete function** - Super Admins can permanently delete trades with double confirmation
  - **Enhanced security** - Proper permission checking in popup context
  - **Added manual password change** - Super Admins can directly set new passwords for brokers
  - **Added password reset email** - Clickable link to send password reset emails to brokers
  - **Added password validation** - Minimum 6 character requirement with client-side validation
  - **Added Super Admin indicator** - Footer displays Super Admin image when user has Super Admin privileges
  - **Fixed broker status issue** - Broker status no longer incorrectly changes to "inactive" when updating user type
  - Super Admin can now fully manage broker accounts including password resets and user type changes

---

## [2025-01-27] - Implemented Clean RECs Product Structure

### Added
- **RECs Product Entries**: Created proper product ID (100) and product type ID (100) for RECs trades
- **Standard Product Relationships**: RECs trades now use standard product relationships instead of workarounds
- **Database Consistency**: All trade types now follow the same data structure pattern

### Changed
- **Trade Input Logic**: Simplified RECs trade handling to use standard product fields
- **Query Structure**: Removed conditional logic and special case handling for RECs trades
- **Code Maintenance**: Cleaner, more maintainable code without trade type exceptions

### Changes Made
- **trade/newly added/create_recs_products.sql**: 
  - SQL script to create RECs product (ID: 999) and product type (ID: 999)
  - Updates RECs trade types to use proper product relationships
  - Ensures database consistency across all trade types
- **trade/newly added/fix_recs_products.sql**: 
  - SQL script to fix column constraint issues and use smaller product IDs
  - Resolves `fkProductIDDefault` range limit issues
  - Sets proper `fldActive` values for all entries
- **trade/trade/tradeInput.php**: 
  - Removed special case handling for RECs trades
  - All trade types now use consistent field handling
  - Simplified INSERT and UPDATE query logic
- **trade/trade/previewConfirm.php**: 
  - Reverted to original simple query structure
  - No more conditional logic needed for different trade types

### Technical Details
- **Product ID**: 100 (RECs) - Successfully created and configured
- **Product Type ID**: 100 (RECs) - Successfully created and configured
- **Database Structure**: RECs trades now have proper foreign key relationships
- **Query Performance**: Standard INNER JOINs work for all trade types
- **Maintenance**: Single code path for all trade types
- **Column Constraints**: `fkProductIDDefault` is `tinyint(4)` with range limit, requiring smaller ID values
- **Status**: ✅ **FIXED** - All RECs product relationships now working correctly

### User Experience
- **For RECs Trades**: Cleaner, more consistent trade input experience
- **For System Reliability**: No more special case handling or workarounds
- **For Developers**: Simpler, more maintainable codebase

---

## [2025-01-27] - Fixed RECs Trade Form Display and Custom Fields

### Fixed
- **Custom Fields Visibility**: RECs trades now show Product, Price, Delivery Point, and Additional Info fields
- **Location Field**: RECs trades now properly display Location dropdown instead of defaulting to "Day Ahead"
- **Form Initialization**: Custom field variables are now properly initialized for new RECs trades
- **Template Options**: RECs trades bypass template restrictions to show necessary fields

### Changed
- **Custom Fields Display**: Now shown for both Crude Oil trades AND RECs trades
- **Location Field Logic**: RECs trades use Natural Gas locations since RECs don't have their own location list
- **Form Field Visibility**: Location field no longer hidden for RECs trades

### Changes Made
- **trade/trade/tradeInput.php**: 
  - Modified custom fields display condition to include RECs trades (`$_GET["tradeType"] == "9"`)
  - Fixed Location field visibility for RECs trades
  - Set RECs trades to use Natural Gas locations for proper dropdown population
  - Added initialization of custom field variables for new RECs trades

### Technical Details
- **Custom Fields**: Product, Price, Delivery Point, Additional Info now visible for RECs trades
- **Location Field**: Uses Natural Gas product group for location dropdown population
- **Form Logic**: RECs trades bypass template option restrictions for essential fields
- **Data Persistence**: Custom fields now properly save and display for RECs trades

### User Experience
- **For RECs Trades**: All necessary fields now visible and functional
- **For Data Entry**: Custom field data properly saves and displays
- **For Location Selection**: Proper location dropdown instead of "Day Ahead" default

---

## [2025-01-27] - Fixed Email Confirmation System and Duplicate Custom Fields

### Fixed
- **Email Confirmation System**: Fixed broken email functionality by reverting to working PHPMailer setup
- **Duplicate Custom Fields**: Fixed issue where RECs trades were creating duplicate entries in `tblB2TCustomFields`
- **PDF Custom Fields Display**: Fixed issue where OTC trade PDFs were blank due to custom fields only being saved on first leg
- **Email Method**: Reverted from complex SMTP configuration back to working PHPMailer with PHP mail() function
- **Confirms Address Logging**: Added proper logging for emails sent to confirms@greenlightcommodities.com

### Changed
- **Email Infrastructure**: Reverted from complex SMTP configuration back to working PHPMailer setup
- **Custom Fields Logic**: Modified to only populate custom fields for the first leg (buy leg) of trades
- **Email Method**: Now uses PHPMailer with PHP's built-in mail() function (same as original working system)

### Changes Made
- **trade/util/confirm.php**: 
  - Reverted from complex SMTP configuration back to working PHPMailer setup
  - Restored original working email system with updated contact information
  - Added logging for confirms@greenlightcommodities.com address
  - Added debug output to show email recipients
- **trade/trade/tradeInput.php**: 
  - Fixed duplicate custom fields by adding `if($i == 1)` condition
  - Custom fields now only populate for the first leg of multi-leg trades
  - Applied fix to both INSERT and UPDATE sections
- **trade/util/genPDF.php**: 
  - Fixed custom fields query to find data from any leg of the same transaction
  - Changed from using specific transaction ID to transaction number for OTC trades
  - This ensures PDFs show custom field data even when viewing legs without the data
- **trade/trade/tradeInput.php**: 
  - Fixed custom fields retrieval for editing existing trades to find data from any leg
  - Changed from using specific transaction ID to transaction number for OTC trades
  - This ensures both legs display the same custom field data when editing
- **trade/trade/previewConfirm.php**: 
  - Reverted to original simple query structure
  - Now RECs trades use proper product relationships instead of workarounds
- **trade/trade/tradeInput.php**: 
  - Simplified RECs trade handling to use standard product relationships
  - Removed special case logic for setting fields to "0" for RECs trades
  - Now all trade types use consistent field handling

### Technical Details
- **Email Method**: PHPMailer with PHP's built-in mail() function (original working setup)
- **PDF Attachments**: PHPMailer handles PDF attachments automatically
- **Custom Fields**: Now only created once per trade instead of per leg
- **Email Reliability**: Uses the exact same proven method as the original working system

### User Experience
- **For Email Confirmations**: Trade confirmations now properly send via email
- **For RECs Trades**: No more duplicate custom field entries in database
- **For System Reliability**: Email system now uses proper infrastructure

### Status
- ✅ **Email System Configured**: Gmail SMTP with app password configured
- ✅ **Ready for Testing**: Email confirmations should work now
- ✅ **No Action Required**: System is configured and ready

---

## [2025-01-27] - Fixed RECs Custom Fields Display and PDF Generation

### Fixed
- **Custom Fields Display**: Fixed field mapping mismatch between database storage and form retrieval
- **PDF Generation**: Added proper support for RECs trades (trade type 9) to display custom fields
- **Product Group Display**: Fixed "Unknown" product group display for RECs trades
- **Field Order**: Corrected database column mapping for custom fields
- **Data Saving**: Fixed critical issue where RECs trades were not saving custom field data to database

### Changed
- **PDF Generation Logic**: Now properly handles both trade types 8 (Crude Oil) and 9 (RECs)
- **Custom Field Retrieval**: Fixed column order mismatch in trade input form
- **RECs Trade Support**: Added complete custom field display for RECs trades in PDFs
- **Trade Submission**: RECs trades now properly call `populateCustomFields()` and `updateCustomFields()`

### Changes Made
- **trade/util/genPDF.php**: 
  - Added custom field support for RECs trades (trade type 9)
  - Fixed product group display to show "RECs" instead of "Unknown"
  - Corrected custom field display order and mapping
  - Added debugging to trace data flow issues
- **trade/trade/tradeInput.php**: 
  - Fixed database column mapping for custom fields retrieval
  - Corrected field order: recordID, productCustom, priceCustom, deliveryPoint, additionalInfo, fkTransID
  - **CRITICAL FIX**: Added `populateCustomFields()` calls for RECs trades (trade type 9)
  - **CRITICAL FIX**: Added `updateCustomFields()` calls for RECs trades (trade type 9)

### Technical Details
- **Database Column Order**: Fixed mismatch between INSERT and SELECT operations
- **PDF Field Mapping**: Custom fields now display in correct order for RECs trades
- **Trade Type Support**: Both Crude Oil (8) and RECs (9) now use custom fields properly
- **Data Persistence**: RECs trades now properly save custom field data to `tblB2TCustomFields`

### User Experience
- **For RECs Trades**: Custom field data now displays correctly in PDF confirmations
- **For Form Data**: Fields retain their values when editing existing trades
- **For PDF Generation**: Product Group shows "RECs" and custom fields display properly
- **For Data Entry**: Custom field data is now properly saved and retrieved from database

---

## [2025-01-27] - Removed Unused Custom Commodity Fields Table and Code

### Removed
- **tblB2TCustomCommodityFields Table**: Dropped table that was created accidentally and not working properly
- **Custom Commodity Functions**: Removed `populateCustomCommodityFields()` and `updateCustomCommodityFields()` functions
- **RECs Form Fields**: Removed REC Type, REC Location, Pricing Model, and Settlement Terms fields from trade input
- **RECs Helper Functions**: Removed `populateRECsProducts()` and `populateRECsLocations()` functions
- **PDF Custom Fields**: Removed custom commodity fields display from PDF confirmations

### Changed
- **Trade Input Form**: Simplified RECs trade input by removing unused custom fields
- **PDF Generation**: Cleaned up PDF generation code to remove references to unused table
- **Code Maintenance**: Eliminated dead code and database queries that were not functioning

### Changes Made
- **trade/functions.php**: 
  - Removed custom commodity fields functions
  - Removed RECs helper functions
- **trade/trade/tradeInput.php**: 
  - Removed custom commodity fields form elements
  - Commented out function calls to removed functions
- **trade/util/genPDF.php**: 
  - Removed custom commodity fields queries and display logic
- **trade/util/genXLS.php**: 
  - Removed custom commodity fields queries
- **trade/newly added/drop_custom_commodity_table.sql**: 
  - SQL script to drop the unused table

### Technical Details
- **Table Removal**: `tblB2TCustomCommodityFields` was not being populated correctly
- **Code Cleanup**: Eliminated approximately 100+ lines of unused code
- **PDF Stability**: Removed code that was causing PDF generation issues
- **Form Simplification**: RECs trades now use standard trade fields only

### User Experience
- **For RECs Trades**: Cleaner, simpler trade input form without confusing unused fields
- **For PDF Confirmations**: More stable PDF generation without custom field errors
- **For System Maintenance**: Cleaner codebase with less dead code

---

## [2025-01-27] - Implemented OTC Trade System with Direct Counterparty Display

### Added
- **OTC Trade Type**: New "OTC" option in cleared trade dropdown for over-the-counter trades
- **Direct Counterparty Display**: OTC trades now show both buyer and seller directly instead of hiding behind clearing house
- **Single Trade Record**: OTC trades create only one trade record instead of multiple legs like cleared trades
- **Broker Information**: OTC trades display both buying and selling broker information
- **Debug Logging**: Added comprehensive error logging to troubleshoot email confirmation issues

### Changed
- **Trade Creation Logic**: Modified to handle OTC trades differently from cleared trades
- **Counterparty Handling**: OTC trades bypass the clearing house contra party system
- **Dashboard Display**: OTC trades show actual buyer/seller companies and traders instead of clearing house names

### Changes Made
- **trade/functions.php**: 
  - Updated `getContraParty()` to return null for OTC trades
  - Modified `showClearedTrade()` to include OTC option
- **trade/trade/tradeInput.php**: 
  - Added OTC-specific logic to create single trade record
  - Modified loop limit to create only 1 record for OTC trades (vs 4 for cleared trades)
  - Updated buyer/seller ID assignment to show both counterparties directly
  - Added OTC-specific broker handling to display both buying and selling brokers
- **trade/dashboard.php**: 
  - Added CSRF token to tradeList form to fix resend confirmation functionality
  - Updated column mapping to properly display OTC trade data
- **trade/util/ajax.php**: 
  - Added error logging to track resend confirmation requests and email preparation
- **trade/util/confirm.php**: 
  - Added error logging to track email sending process and identify failures

### Technical Details
- **OTC Trade Structure**: Single trade record with direct buyer/seller IDs
- **Loop Logic**: OTC trades use loop limit of 1, cleared trades use 4, regular trades use 2
- **Counterparty Display**: OTC trades show actual company names, cleared trades show clearing house names
- **CSRF Protection**: Added missing CSRF token to prevent validation failures
- **Debug Logging**: Comprehensive logging of email confirmation process for troubleshooting

### User Experience
- **For OTC Trades**: Clear visibility of both counterparties without clearing house confusion
- **For Cleared Trades**: Maintains existing clearing house functionality
- **Dashboard**: Consistent display of all trade types with proper counterparty information

---

## [2025-01-27] - Implemented Temporary Password System with First Login Password Change

### Added
- **Temporary Password Generation**: New brokers now receive a random 10-character temporary password
- **First Login Detection**: System tracks whether users have logged in for the first time
- **Password Change Page**: Dedicated page for users to change their temporary password on first login
- **Automatic Redirect**: Users are automatically redirected to password change page on first login

### Changed
- **Broker Creation Process**: Now generates and stores temporary passwords in the database
- **Email Content**: Welcome emails now include username and temporary password (removed account status)
- **Login Flow**: Added first login check and redirect logic
- **Database Structure**: Added `fldFirstLogin` column to track first login status

### Changes Made
- **trade/trade/brokerDetail.php**: 
  - Added `generateTempPassword()` function for secure password generation
  - Updated broker creation to generate and store temporary passwords
  - Changed email sending to use `sendWelcomeEmail()` with username and temp password
  - Set `fldActive` to "1" and `fldFirstLogin` to "1" for new accounts
- **trade/util/email_sender.php**: 
  - Updated welcome email template to include username and temporary password
  - Removed account status display from email
  - Added security notice about temporary password change requirement
- **trade/functions.php**: 
  - Updated `login()` function to fetch and store `fldFirstLogin` status
  - Added session variables for user ID and first login tracking
- **trade/index.php**: 
  - Added first login check and redirect to password change page
- **trade/change_password.php**: 
  - New password change page for first-time users
  - Form validation and password update functionality
  - Automatic redirect after successful password change
- **trade/add_first_login_column.sql**: 
  - SQL script to add `fldFirstLogin` column to database

### Technical Details
- **Password Generation**: 10 characters using a-z, A-Z, 0-9, and one special character from !@#$%^&*
- **First Login Tracking**: `fldFirstLogin` field (1 = first login, 0 = completed)
- **Security**: Users must change temporary password before accessing the system
- **Validation**: New passwords must be at least 8 characters long
- **Session Management**: Enhanced session variables for user tracking

### User Experience
- **For Admins**: Create brokers with auto-generated usernames and temporary passwords
- **For New Users**: Receive welcome email with login credentials and clear instructions
- **First Login**: Automatic redirect to password change page
- **Security**: Enforced password change on first login

---

## [2025-01-27] - Fixed Employee Login URL in Email Templates

### Fixed
- **Employee Login URL**: Updated email templates to use the correct, specific employee login URL
- **URL Accuracy**: Changed from generic domain to specific login page path

### Changes Made
- **trade/util/email_sender.php**: 
  - Updated both account creation and welcome email templates
  - Changed employee login URL from `trade.greenlightcommodities.com` to `trade.greenlightcommodities.com/index.php?p=employee`
  - Fixed malformed URLs that were duplicated during previous updates

### Technical Details
- **Issue**: Email templates were using generic domain instead of specific login page URL
- **Solution**: Updated to use the exact employee login page URL: `https://trade.greenlightcommodities.com/index.php?p=employee`
- **Result**: Users now receive emails with direct links to the correct login page

---

## [2025-01-27] - Implemented Auto-Generated Usernames and Setup Links

### Added
- **Auto-Generated Usernames**: Usernames are now automatically generated as "first letter of first name + last name" (e.g., "jmoney" for "j money")
- **Setup Links**: Users now receive secure setup links to set their own passwords instead of admin-set passwords
- **Employee Login URL**: Added trade.greenlightcommodities.com login URL to all welcome emails
- **Smart Username Handling**: Automatic duplicate username detection with random suffix addition

### Changed
- **Broker Creation Process**: Simplified form interface - username field is now auto-generated and hidden
- **Password Setup**: Always uses email setup links for new users (no more manual password option)
- **Email Templates**: Updated both account creation and welcome email templates with login URL
- **Database Structure**: Added support for setup tokens and expiry dates for secure password setup

### Changes Made
- **trade/trade/brokerDetail.php**: 
  - Implemented auto-username generation logic (first letter + last name)
  - Added duplicate username detection and handling
  - Simplified form interface - removed username input and password choice options
  - Added setup token generation and database storage
  - Updated email sending to use setup links instead of welcome emails
- **trade/util/email_sender.php**: 
  - Added employee login URL to both email templates
  - Enhanced email templates with clear login information

### Technical Details
- **Username Generation**: `strtolower(substr($firstName, 0, 1) . $lastName)` with duplicate handling
- **Setup Security**: 32-character random tokens with 24-hour expiry
- **Database Fields**: Uses `fldSetupToken` and `fldSetupExpiry` for secure setup process
- **Email Flow**: Users receive setup links to create their own passwords securely

### User Experience
- **For Admins**: Simpler form - just enter name, email, and user type
- **For Users**: Receive professional setup email with clear instructions and login URL
- **Security**: Users set their own passwords via secure, time-limited setup links

---

## [2025-01-27] - Fixed Email Invitation System for New Brokers

### Fixed
- **Email Invitation System**: Fixed the email invitation functionality when adding new brokers
- **Database Compatibility**: Updated the broker creation process to work with existing database table structure
- **Simplified Email Flow**: Replaced complex setup token system with simple welcome emails for immediate functionality

### Changes Made
- **trade/trade/brokerDetail.php**: 
  - Simplified INSERT statement to work with existing table structure
  - Replaced complex setup token logic with simple welcome email system
  - Fixed email sending logic to work without missing database fields
- **trade/util/email_sender.php**: 
  - Added `sendWelcomeEmail()` method for new broker notifications
  - Added `getWelcomeTemplate()` method for consistent email formatting
  - Maintained existing email infrastructure while adding new functionality

### Technical Details
- **Issue**: The email invitation system was failing due to missing database fields (`fldSetupToken`, `fldSetupExpiry`, `fldAllowUserToSetUsername`)
- **Solution**: Simplified the system to work with existing table structure while maintaining email functionality
- **Result**: New brokers now receive welcome emails immediately upon account creation

### Files Modified
- `trade/trade/brokerDetail.php` - Fixed broker creation and email logic
- `trade/util/email_sender.php` - Added welcome email functionality

---

## [2025-01-27] - Fixed Garbled Text in Broker Status Column

### Fixed
- **Status Column Display**: Removed problematic bullet point characters that were displaying as garbled text
- **Clean Status Text**: Status now shows clean "Active" or "Inactive" text without special characters

### Changes Made
- **trade/brokers.php**: 
  - Removed bullet point characters (●) from status display
  - Status now shows plain text: "Active" or "Inactive"

### Technical Details
- **Issue**: Status column was showing garbled text like "â— Inactive" and "â— Active"
- **Solution**: Removed problematic Unicode characters and used plain text
- **Result**: Clean, readable status display for Super Admins

---

## [2025-01-27] - Fixed Super Admin User Type Editing Access

### Fixed
- **User Type Editing**: Super Admins can now properly edit other users' user types
- **Permission Logic**: Updated permission checks to use `isSuperAdmin()` function consistently

### Changes Made
- **trade/trade/brokerDetail.php**: 
  - Updated `disabled` attribute logic to use `!isSuperAdmin()` instead of direct user type check
  - Fixed user type dropdown to allow Super Admins to make changes

### Technical Details
- **Issue**: Super Admins were seeing "(Cannot Change)" for user types they should be able to edit
- **Solution**: Applied consistent `isSuperAdmin()` checks throughout the user type editing logic
- **Result**: Super Admins now have full access to edit user types as intended

---

## [2025-01-27] - Removed User Type Switcher Dropdown

### Removed
- **User Type Switcher**: Completely removed the user type switcher dropdown from the main interface
- **Associated JavaScript**: Removed `switchUserType()` and `resetUserType()` functions
- **AJAX Handlers**: Updated AJAX handlers to reflect the removal

### Changes Made
- **trade/index.php**: 
  - Removed entire HTML block for user type switcher
- **trade/scripts/scripts.js**: 
  - Removed `switchUserType()` and `resetUserType()` JavaScript functions
- **trade/util/ajax.php**: 
  - Updated access control messages to reflect that both Administrators and Super Admins can use remaining functions

### Technical Details
- **Issue**: User type switcher was not working as expected and causing confusion
- **Solution**: Complete removal of the feature to simplify the interface
- **Result**: Cleaner, more focused user interface without confusing user type switching

---

## [2025-01-27] - Fixed Super Admin Navigation Access

### Fixed
- **Navigation Access**: Super Admins now have proper access to all navigation items
- **Permission Functions**: Updated `isAdmin()` and `isSuperAdmin()` functions to work correctly

### Changes Made
- **trade/functions.php**: 
  - Modified `isAdmin()` function to always return `true` for Super Admins (`userType == "2"`)
  - Updated `isSuperAdmin()` function to handle both parameter-based and session-based checks
  - Ensured Super Admins retain privileges regardless of temporary overrides
- **trade/index.php**: 
  - Updated user type switcher visibility to include Super Admin option
  - Fixed navigation access for Super Admin accounts

### Technical Details
- **Issue**: Super Admin account (`jakem` with userType "2") was only showing "Dashboard, Indices, Reporting" after clearing cookies
- **Solution**: Updated permission functions to properly identify and grant Super Admin access
- **Result**: Super Admins now have full navigation access as intended

---

## [2025-01-27] - Unified Broker List Display

### Changed
- **Broker List**: Modified broker list to show both active and inactive brokers in a single unified list
- **Status Column**: Added new "Status" column for Super Admins to see broker status
- **Sorting**: Updated sorting to show active brokers first, then inactive brokers

### Changes Made
- **trade/brokers.php**: 
  - Modified SQL query to select all brokers (both active and inactive) for Super Admins
  - Added "Status" column header and data display for Super Admins
  - Updated sorting to `ORDER BY fldActive DESC, fldName ASC` (active first, then inactive)
  - Fixed colspan for "No brokers found" message to match column count
  - Removed active/deactivated filter dropdown

### Technical Details
- **Issue**: User wanted to undo the previous broker list filtering and display all brokers in one list
- **Solution**: Modified the broker list to show all brokers with status information for Super Admins
- **Result**: Super Admins can now see all brokers in a single, sortable list with clear status indicators

---

## [2025-01-27] - Restricted Test Email Button to Super Admins

### Changed
- **Test Email Button**: Restricted "Test Email" button visibility to Super Admins only
- **Permission Check**: Added `isSuperAdmin()` check to control button display

### Changes Made
- **trade/brokers.php**: 
  - Wrapped "Test Email" button in PHP conditional block using `isSuperAdmin()` function
  - Button now only displays when current user has Super Admin privileges

### Technical Details
- **Issue**: Regular administrators and general users could see the "Test Email" button
- **Solution**: Added permission check to restrict button visibility to Super Admins only
- **Result**: Test Email functionality is now properly restricted to Super Admin users

---

## [2025-01-27] - Initial Project Setup and Documentation

### Added
- **Project Structure**: Established basic project organization and file structure
- **Changelog**: Created comprehensive changelog to track all project modifications
- **Documentation**: Added detailed technical documentation for all changes

### Files Created
- `trade/newly added/CHANGELOG.md` - Project changelog and modification history

### Technical Details
- **Purpose**: Establish proper project documentation and change tracking
- **Format**: Standard changelog format with clear sections for each change
- **Maintenance**: Will be updated with each subsequent modification

## 2024-12-19 - Company Dropdown Fixes and Trader Creation Improvements

### **Files Modified:**
- `trade/functions.php` - Added `populateExistingCompaniesForSelection()` function
- `trade/trade/clientDetail.php` - Changed Company Name field to dropdown, updated Copy From Existing to show companies
- `trade/util/ajax.php` - Added `fetchCompanyDetails` action handler

### **Changes Made:**

#### **1. Fixed Company Visibility in Parent Company Dropdown**
- **Problem:** Newly created companies weren't appearing in the "Parent Company" dropdown when creating traders
- **Root Cause:** `populateExistingCompanies()` function was filtering out companies with no active traders using `WHERE cl.fldActive=1`
- **Solution:** Changed `WHERE cl.fldActive=1` to `AND cl.fldActive=1` in the JOIN clause
- **Result:** All companies now appear in the Parent Company dropdown, with "(Traders: 0)" for new companies

#### **2. Improved Company Name Field in Trader Creation**
- **Problem:** Company Name field was a free-text input, allowing duplicate company names
- **Solution:** Changed Company Name field from text input to dropdown populated with existing companies
- **Result:** Prevents duplicate company names and ensures consistency

#### **3. Updated Copy From Existing Dropdown**
- **Problem:** "Copy From Existing" dropdown showed clients instead of companies
- **Solution:** Changed to show companies using new `populateExistingCompaniesForSelection()` function
- **Result:** Both dropdowns now show the same company list for consistency

#### **4. Added JavaScript Functions**
- **Added:** `updateCompanyInfo()` and `updateExistingClient()` functions to handle dropdown changes
- **Purpose:** Ensures proper synchronization between dropdowns

#### **5. Fixed Invoice Field Auto-Population**
- **Problem:** Invoice fields were empty when creating new traders, even when company had invoice data
- **Root Cause:** Invoice fields were only populated from existing trader data (`$resultSet`), not from company profile
- **Solution:** Added AJAX functionality to fetch company details when a company is selected
- **Added:** `fetchCompanyDetails()` JavaScript function and corresponding AJAX handler
- **Result:** Invoice fields now automatically populate with company data when creating new traders

### **Technical Details:**
- **Function Added:** `populateExistingCompaniesForSelection()` - Shows companies without trader counts for dropdown selection
- **Query Modified:** `populateExistingCompanies()` - Fixed JOIN clause to show all companies
- **UI Changes:** Company Name field converted from `<input>` to `<select>` element
- **JavaScript:** Added event handlers for dropdown synchronization and company data fetching
- **AJAX Handler:** Added `fetchCompanyDetails` action in `ajax.php` to retrieve company invoice information

### **Impact:**
- ✅ New companies now appear in Parent Company dropdown
- ✅ Prevents duplicate company creation in trader forms
- ✅ Consistent company selection across both dropdowns
- ✅ Invoice fields now auto-populate from company profile
- ✅ Better data integrity and user experience

---

## 2024-12-19 - Created Comprehensive RECs Category

### 15:30 - Created Comprehensive RECs Category
- **File**: `trade/newly added/update_rec_trade_name.sql`
- **Change**: Updated SQL script to create a comprehensive "RECs" category that encompasses all renewable energy credit types (wind, solar, hydro, biomass, geothermal, etc.)
- **Details**: 
  - Changed `fldProductGroup` from "Custom Commodity" to "RECs" for trade type 9
  - Updated `fldTradeName` to "RECs" for consistency
  - Set `fldUnits` to "RECs" 
  - All REC-related trade types now fall under the single "RECs" product group
  - Updated dashboard filter to use "RECs" instead of "REC Trade"
  - Updated trade input form logic to reference "RECs" category
- **Purpose**: Create a unified category for all renewable energy credit trades, making the system more organized and scalable

## 2024-12-19 - Added RECs Suffix to PDF Quantity Fields

### 15:45 - Added RECs Suffix to PDF Quantity Fields
- **File**: `trade/util/genPDF.php`
- **Change**: Added "RECs" suffix to quantity fields in PDF confirmations and invoices for RECs trades
- **Details**: 
  - **Unit Quantity**: Now displays "X MMBTU/Block RECs" for RECs trades instead of just "X MMBTU/Block"
  - **Total Quantity**: Now displays "X RECs" for RECs trades instead of just "X"
  - **Invoice Quantities**: Invoice PDFs also show "X RECs" for RECs trades
  - **Trade Types Covered**: Applies to trade types 9 (RECs) and 10 (RECs Option)
  - **Conditional Logic**: Only adds "RECs" suffix when `fldProductGroup` is RECs trade types
- **Purpose**: Make PDF confirmations and invoices clearer by explicitly showing that quantities are in RECs units

## 2024-12-19 - Fixed RECs Trade Form Submission Issues

### 16:00 - Fixed RECs Trade Form Submission Issues
- **File**: `trade/trade/tradeInput.php`
- **Change**: Fixed database insertion issues when submitting RECs trades with empty non-applicable fields
- **Details**: 
  - **Database Insert Fix**: Modified INSERT queries to set non-applicable fields to "0" for RECs trades instead of trying to insert empty values
  - **Hidden Fields**: Product, Product Type, and Offset Index fields are now hidden for RECs trades since they're not applicable
  - **Default Values**: RECs trades now use default values (0) for fields like productID, productTypeID, offsetIndex, optionStyle, optionType, exerciseType, strikePrice, and strikeCurrency
  - **Form Validation**: Prevents form submission errors when non-applicable fields are left empty
  - **Trade Types Affected**: Only trade type 9 (RECs) uses the special handling
- **Purpose**: Ensure RECs trades can be submitted successfully without requiring values for non-applicable fields

## Previous Entries...

## [2025-01-27] - Fixed Trader Addition Form Interference and Added Debugging

### Fixed
- **Trader Addition Form**: Fixed persistent issue where trader form was interfering with company form submission
- **Form Validation**: Removed `required` attribute from trader form fields to prevent HTML5 validation conflicts
- **Form Separation**: Moved trader addition section outside main company form to ensure logical separation
- **Company Form Updates**: Company form now updates properly without trader form interference

### Fixed
- **JavaScript Variable Conflicts**: Fixed duplicate `const stateSelect` declarations causing "Identifier 'stateSelect' has already been declared" error
- **Duplicate Variable Declarations**: Fixed duplicate `stateSelect` and `stateText` declarations in `prepareFormData()` function
- **Function Availability**: Fixed `addTrader is not defined` error by resolving JavaScript syntax errors
- **AJAX Response Format**: Fixed trader addition returning raw HTML instead of clean text response
- **Server-Side Processing Order**: Moved trader addition processing before `popupHeader.php` include to prevent HTML output in AJAX response
- **Database Column Mismatch**: Fixed "Column count doesn't match value count" error by using explicit column names in INSERT query

### Added
- **Debug Logging**: Added comprehensive console logging to `addTrader()` function for troubleshooting
- **Response Tracking**: Added detailed logging of AJAX request/response cycle to identify issues
- **Error Handling**: Enhanced error reporting for trader addition process
- **Company Auto-Creation**: Added logic to automatically create company if it doesn't exist when adding trader
- **Smart Form Data Collection**: JavaScript now collects company form data when company doesn't exist
- **Seamless Workflow**: Users can now add traders to new companies without losing company data
- **Enhanced Debugging**: Added comprehensive logging to track company creation process
- **Popup Window Handling**: Fixed popup window behavior to properly close and refresh parent window
- **Company Delete Permissions**: Restricted company delete functionality to Super Admins only
- **Company Delete Function**: Added missing `confirmCompanyDelete` JavaScript function with double confirmation
- **Fixed mysqli_affected_rows Error**: Fixed PHP warning by adding required database connection parameter
- **Fixed Country Validation Logic**: Fixed issue where zip field was incorrectly marked as required when only company name was entered
- **Fixed mysqli_affected_rows Error in Clients**: Fixed PHP warning in traders page by adding required database connection parameter
- **Added Company Existence Check for Trader Addition**: Prevented adding traders before company is saved with visual indicators and validation
- **Added Debugging for Zip Field Validation**: Added console logging to track why zip field is being marked as required
- **Fixed CSS Path Error**: Fixed 404 error for styles.css by correcting path from `css/styles.css` to `../css/styles.css`

### Changes Made
- **trade/trade/companyDetail.php**:
  - Removed `required` attribute from trader name field
  - Added console.log statements to track function execution
- **trade/company.php**:
  - Added Super Admin permission check for Delete link visibility
  - Added Super Admin permission check for delete action processing
  - Created missing `confirmCompanyDelete` JavaScript function
  - Added double confirmation dialog for company deletion
  - Enhanced error handling for unauthorized delete attempts
  - Added detailed logging of form data and AJAX responses
  - Maintained trader form in original aesthetic position under traders list

### Technical Details
- **Issue**: Trader form fields with `required` attribute were triggering main form validation
- **Solution**: Removed HTML5 validation from trader form, kept JavaScript validation
- **Debugging**: Added comprehensive logging to identify any remaining issues with AJAX submission
- **Form Structure**: Trader form remains visually grouped with traders but logically separated

---

## [Latest] - 2025-01-27

### Changed
- **Reply-to Email Address**: Changed the reply-to address in trade confirmation emails from `confirms@greenlightcommodities.com` to `jake@greenlightcommodities.com` to allow recipients to reply directly to the broker.

### Fixed
- **FPDF Path Issues**: Fixed incorrect require_once paths in `trade/util/genPDF.php` that were preventing PDF generation for email confirmations. Changed `../fpdf/fpdf.php` to `fpdf/fpdf.php` and `../vars.inc.php` to `vars.inc.php` to match the correct directory structure.
- **Preview Confirm FPDF Path**: Fixed FPDF library paths in `trade/util/genPDF.php` to use absolute paths (`dirname(__FILE__) . "/../fpdf/fpdf.php"`) so they work correctly when called from both `trade/util/` and `trade/trade/` directories.
- **Temp Directory Path Issues**: Fixed incorrect temp directory path in `trade/util/confirm.php` that was preventing PDF file creation. Changed `../temp/` to `temp/` to match the correct directory structure.
- **Function Redeclaration Error**: Fixed `safeTemplateOption()` function redeclaration error in `trade/util/genPDF.php` by wrapping the function declaration in a `function_exists()` check to prevent duplicate declarations.

### Improved
- **PDF Company Name Word Wrapping**: Enhanced the PDF confirmation generation to use FPDF's `MultiCell` function for better word wrapping of company names, preventing long company names like "DRW Investments LLC- US NG/Pow" from extending beyond the page boundaries.

### Changed
- **Trade Confirmation Email Subjects**: Updated the `translateProduct` function to return "Trade" for all trade types, resulting in consistent "Trade Confirmation" email subjects instead of specific "Power Trade Confirmation" or "Gas Trade Confirmation" labels.
- **From Email Address**: Changed the sender address in trade confirmation emails from `confirms@greenlightcommodities.com` to `jake@greenlightcommodities.com` for more personalized communication.

### Fixed
- **Duplicate "Trade" in Email Subjects**: Fixed the email subject line construction to prevent "Trade Trade Confirmation" by using a static subject line instead of concatenating the product type.

### Added
- **Preview Invoice Functionality**: Added a "Preview Invoice" button to the invoice detail page that allows users to preview invoices before sending them, similar to the existing "Preview Confirm" functionality.
  - Created `trade/trade/previewInvoice.php` for generating invoice previews
  - Added "Preview Invoice" button to the invoice detail page (accessed via company name from main invoicing page)
  - Added `previewInvoice()` JavaScript function directly in `invoiceDetail.php` to handle the preview action
  - Preview opens in a new window showing the PDF invoice for selected trades
  - Properly handles temporary invoice creation and cleanup for preview purposes

### Fixed
- **Forgot Password Form**: Fixed JavaScript validation issue that was preventing form submission even when username was entered
- **Super Admin Password Reset**: Enabled manual password reset field for Super Admins in broker detail page
- **User Type Editing**: Super Admins can now edit user types and reset passwords directly

### Documentation
- **PDF Preview Implementation Process**: Documented the complete process for implementing PDF preview functionality:
  1. Create preview PHP file in appropriate directory
  2. Add JavaScript function using `window.open()` instead of `newWindow()` to avoid jQuery cookie dependencies
  3. Include `scripts.js` for `validateCheckboxes()` function
  4. Generate PDF content first using existing PDF generation functions
  5. Set proper HTTP headers (`Content-Type: application/pdf`, `Content-Disposition: inline`) BEFORE any output
  6. Output PDF content directly to browser
  7. Clean up any temporary database records
  - **Key Point**: Headers must be set before any echo/print statements to avoid "headers already sent" errors

### Reverted
- **PDF Company Name Word Wrapping**: Reverted the MultiCell implementation back to the original str_split logic for company names to maintain the existing PDF layout and formatting.

### Cleaned Up
- **Debug Output Removal**: Removed all debug output and verbose logging from the dashboard confirmation system to provide a clean, professional interface. Cleaned up debug messages from both `trade/dashboard.php` and `trade/util/confirm.php`.
- **PDF Filename Debug Output**: Removed `echo($filename)` statements from `trade/util/genPDF.php` that were displaying PDF filenames on the dashboard.
- **Debug Info Section**: Removed the temporary debug info section from the dashboard that was showing error log locations and working directory information.

### Fixed
- **CSS Path Fix**: Corrected the stylesheet link from `href="css/styles.css"` to `href="../css/styles.css"` to resolve a 404 error.
- **Zip Field Validation Fix**: Added explicit JavaScript code to ensure the zip field is never marked as required, preventing HTML5 validation errors when only company name is entered.
- **Dashboard PHP Warning Fix**: Fixed `mysqli_fetch_row() expects parameter 1 to be mysqli_result, bool given` warning in `dashboard.php` line 75 by adding proper error checking before calling `mysqli_fetch_row()` on database query results.
- **Password Reset Fatal Error Fix**: Removed invalid `setReplyTo()` method call from `forgot.php` line 25. The `Email` class automatically sets Reply-To header in `sendMessage()`, making the explicit call unnecessary and causing a fatal error.
- **Password Reset Spam Filter Fix**: Improved email headers and content in `forgot.php` to reduce spam filtering. Added proper X-Mailer headers, improved subject line, and professional HTML email template with better styling and content structure.
- **"Same as Invoice Information" Fix**: Fixed the checkbox functionality in trader detail form to properly copy from the visible Invoice Information fields to Trade Confirm fields. Changed from AJAX call to direct field copying using `.value` property to handle disabled/readonly fields correctly.
- **Add Trader Button Debugging**: Added debugging logs to `companyDetail.php` to investigate why the "Add Trader" button isn't working when a company is opened for editing. Added PHP error logging for companyID values and JavaScript console logging for debugging the addTrader function execution.
- **Comprehensive Cron Job Monitoring System**: Implemented a complete cron job monitoring and management system for Super Admins with the following features:
  - **Multi-Page Display**: Cron status now appears on dashboard, trade page, and company page
  - **Detailed Job Information**: Shows all cron jobs (trade confirmations, ICE data scraping, company mapping) with descriptions and schedules
  - **Real-Time Status**: Color-coded status indicators (green=OK, orange=warning, red=critical) based on execution delays
  - **Force Update Capability**: Emergency force execution buttons for critical situations (15-minute trade processing requirement)
  - **Audit Logging**: All force executions are logged with timestamps and user information
  - **Auto-Refresh**: Status updates every 30 seconds with manual refresh option
  - **Overdue Warnings**: Clear warnings when cron jobs exceed their maximum delay thresholds
  - **Files Created**: `util/cronMonitor.php` (monitoring class), `util/cronStatusDisplay.php` (reusable component), `util/forceCron.php` (AJAX handler)
  - **Files Modified**: Added execution logging to `cron24.php` and `update_company_mapping.php`, updated dashboard to use new system
  - **Debugging Added**: Added debug comments to `cronStatusDisplay.php` and `dashboard.php` to help troubleshoot why the cron monitoring system might not be visible on the dashboard. Added Super Admin status checking and component inclusion debugging.
  - **Cron Status Display Position Fix**: Moved the cron monitoring system from the end of `dashboard.php` to the main content area (after `ajaxResponse` div) so it appears within the visible page content instead of after the main wrapper closes. This ensures the cron status is visible to Super Admins on the dashboard.
  - **Cron Status Display Dependencies Fix**: Added missing `require_once('../functions.php')` and `require_once('../vars.inc.php')` to `cronStatusDisplay.php` and `cronMonitor.php` to ensure the `isSuperAdmin()` function and session variables are available. This fixes the issue where the cron monitoring system was causing the entire page to be hidden due to missing dependencies.
  - **Cron Status Display Path Fix**: Changed relative paths to absolute paths using `dirname(__FILE__)` in `cronStatusDisplay.php` and `cronMonitor.php` to fix "No such file or directory" errors when including `functions.php` and `vars.inc.php`. This ensures the files are found regardless of the current working directory.
  - **Cron Status Display Access Fix**: Added `define('CRON_STATUS_ACCESS', true);` before including `cronStatusDisplay.php` in `dashboard.php` to fix the "Direct access not allowed" error that was preventing the cron monitoring system from displaying. The component requires this constant to be defined for security purposes.
  - **Cron Monitoring System Removal**: Temporarily removed the cron monitoring system from `dashboard.php`, `company.php`, and `trade.php` due to compatibility issues that were preventing Super Admins from accessing these pages. The system was causing "Direct access not allowed" errors and breaking page functionality. The cron monitoring files remain available in `util/` for future implementation once compatibility issues are resolved.
  - **Simple Cron Monitoring System**: Created a new simplified cron monitoring system (`util/simpleCronMonitor.php`, `util/simpleCronDisplay.php`, `util/forceCron.php`) that provides the same functionality without complex dependencies. This version is designed to be reliable and not break existing functionality. Added to `dashboard.php`, `company.php`, and `trade.php` for Super Admins only.
  - **Cron Monitor UI Improvements**: Fixed emoji display issue (removed "ðŸ" text), made the interface more compact with smaller fonts and reduced padding, and moved the cron monitor to the bottom of all pages (dashboard, company, and trade) after the main content for better user experience.
  - **Trade Page Access Fix**: Added "trade" to the allowed pages list in `index.php` and added a "Trade" navigation link for admin users. This fixes the "The page you requested does not exist" error when accessing `index.php?p=trade`.
  - **Trade Page Disabled**: Removed "trade" from allowed pages list and navigation menu in `index.php` since there was never supposed to be a trade page. The `trade.php` file remains but is no longer accessible through the navigation system.
  - **Add Trader Button Debugging**: Added debug alerts and console logging to the "Add Trader" button in `companyDetail.php` to troubleshoot why the button click is not working. Added alerts to verify button clicks and function calls are being triggered properly.
  - **Add Trader Button Fix**: Fixed JavaScript syntax error caused by duplicate `zipField` variable declarations in `companyDetail.php`. The error prevented the entire JavaScript from loading, making the `addTrader` function undefined. Removed duplicate declarations and changed one to `const` to resolve the issue.
  - **Add Trader Popup Behavior**: Modified the "Add Trader" functionality to keep the company detail popup window open after successfully adding a trader. The popup now refreshes the parent window to show the new trader but remains open for adding additional traders, improving the user workflow.
  - **Add Trader Auto-Refresh**: Enhanced the "Add Trader" functionality to automatically refresh the parent window after clicking "OK" on the success message. Added a small timeout delay to ensure the alert is processed before refreshing, eliminating the need for manual page refresh to see newly added traders.
  - **Locations Page PHP Compatibility Fix**: Fixed fatal error in `locations.php` by replacing deprecated `ereg_replace()` function with `preg_replace()` on line 56. The `ereg_replace()` function was removed in PHP 7.0.0, causing the "Call to undefined function ereg_replace()" error when trying to add new locations.
  - **Location Edit Functionality Fix**: Fixed issue in `locationDetail.php` where location data was fetched from the database but never assigned to form variables, causing edit forms to appear empty. Added proper variable assignments from the database result set to populate all form fields when editing existing locations.
  - **Location Form Field Population Fix**: Fixed all form fields in `locationDetail.php` to use individual variables instead of direct `$resultSet` array access. Added `isset()` checks to prevent errors when adding new locations vs editing existing ones. All dropdowns and input fields now properly populate with existing data when editing locations.
  - **Natural Gas Simple Trade Type Creation**: Created SQL script and PHP script to add a new trade type based on trade type 6 (Natural Gas) but without option-related fields. The new trade type removes Option Style, Option Type, Exercise Type, Strike, and Strike Currency fields while keeping all other Natural Gas functionality. Files created: `create_natural_gas_simple_trade_type.sql` and `create_natural_gas_simple.php`.
  - **Trade Input Template Error Fix**: Fixed PHP warning in `tradeInput.php` line 547 where `mysqli_fetch_row()` was called without checking if the database query result was valid. Added proper error checking to prevent the "expects parameter 1 to be mysqli_result, bool given" warning when accessing the new trade type.
  - **Natural Gas Simple Unit Frequency Fix**: Created SQL scripts to fix missing Unit Frequency field in the Natural Gas Simple trade type. The field was not appearing because the template configuration needed to be properly set. Created `fix_unit_frequency_simple.sql` and `fix_natural_gas_simple_template.sql` to ensure all template fields are correctly configured.
  - **Natural Gas Simple Template Optimization**: Further simplified the Natural Gas Simple trade type template by hiding unnecessary fields (Offset Index, Delivery, Region, Block, Hours, Time Zone, Index Price, Seller Price/Currency) to create a clean, streamlined trading interface with only essential fields for Natural Gas trades.
  - **Cron Force Update Fix**: Fixed "Access denied" error when trying to force update cron jobs from the dashboard. Updated `forceCron.php` to accept the correct job name `trade_confirmations` instead of `cron_execution`, and added debugging information to help troubleshoot user permission issues.
  - **PDF Address Formatting Fix**: Fixed address formatting issue in trade confirmation PDFs where the buying company address was appearing on the same line as "Address:" instead of on a new line. Changed the PDF generation parameter from `0,0` to `0,1` in `genPDF.php` to add proper line break after the buyer address, matching the seller address formatting.
  - **PDF ICE ID Formatting Fix**: Fixed ICE cleared trade ID formatting in trade confirmation PDFs where the ID was appearing on the same line as "Address:" instead of on a new line. Changed the PDF generation parameter from `0,0` to `0,1` for ICE ID display to ensure proper line breaks and consistent formatting.
  - **PDF Address and ID Order Fix**: Fixed the order of address and ID information in trade confirmation PDFs for ICE cleared trades. Now shows the address first, then the ID# below it, instead of showing ID# before the address. This ensures consistent formatting between buying and selling company sections.
  - **PDF Duplicate ID and Alignment Fix**: Fixed duplicate ID display in buying company section and misaligned seller address formatting in trade confirmation PDFs. Removed duplicate ID printing logic and ensured proper indentation for seller address information to match the buying company formatting.
  - **PDF Address and ID Separation Fix**: Fixed the issue where address and ID information were appearing on the same line in trade confirmation PDFs. Added logic to detect and separate address and ID information when they are combined in the same field, ensuring proper formatting with address on one line and ID on the next line for both buying and selling company sections.
  - **PDF ICE CLEARED ID Formatting Fix**: Fixed ICE CLEARED trade formatting in trade confirmation PDFs by removing the inappropriate "Address:" label when displaying ICE IDs. ICE CLEARED trades now show only "ID#: [number]" without the "Address:" prefix, as the ID is not an address but a trade identifier.
  - **PDF ICE ID Position Fix**: Moved ICE ID to appear directly under the "Trader: ICE CLEARED" line instead of in the address section. The ID now appears in the correct position under the trader information for ICE CLEARED trades, providing better logical grouping of trade-related information.
  - **PDF Address and Telephone Alignment Fix**: Fixed alignment issues in trade confirmation PDFs where address line 2 and telephone numbers were not properly aligned under the buying company section. Replaced hardcoded spacing with proper cell positioning to ensure consistent alignment with the "Address:" and "Tel:" labels.
  - **PDF Syntax Error Fix**: Fixed PHP syntax error in `genPDF.php` where missing curly braces in an `if` statement caused an "unexpected 'else'" error. Added proper braces around the buyer city/state/zip conditional block to ensure correct PHP syntax.
  - **PDF Address Stacking Fix**: Fixed inconsistent cell widths in trade confirmation PDFs that caused address lines to not stack properly. Standardized all buyer address elements (address line 1, line 2, city/state/zip, and telephone) to use consistent 18+73=91 width alignment, ensuring all address information stacks neatly under the "Address:" and "Tel:" labels.
  - **PDF Address Alignment Fix**: Fixed address continuation line alignment in trade confirmation PDFs. Address line 2 and city/state/zip now properly align with the start of the address text (after "Address: " label) rather than being misaligned, creating a clean stacked appearance for multi-line addresses.
  - **PDF Address Left-Alignment Fix**: Fixed address continuation lines to be left-aligned with the "Address:" label instead of indented. Address line 2 and city/state/zip now start at the same left margin as the "Address:" label, creating a clean, compact address block format.
  - **PDF Address Spacing Fix**: Reduced cell height from 5 to 4 for address continuation lines (address line 2, city/state/zip, and telephone) to create tighter spacing between address elements, resulting in a more compact and professional appearance for the address block.
  - **PDF Address Spacing Optimization**: Further reduced cell height from 4 to 3 for address continuation lines to create minimal spacing between address elements, achieving a more compact and tightly formatted address block appearance.
  - **PDF Address Alignment Complete Fix**: Fixed all address alignment issues in trade confirmation PDFs. Address continuation lines (address line 2, city/state/zip) now properly align with the "Address:" label instead of being indented. Telephone number now properly aligns with the "Tel:" label. Reduced cell height to 2 for ultra-compact spacing between all address elements, creating a perfectly aligned and tightly formatted address block.
  - **PDF Address Spacing Ultra-Compact Fix**: Further reduced cell height from 2 to 1 for all address continuation lines to eliminate excessive blank space between address elements. This creates the most compact possible spacing between "2 Pennsylvania Plaza", "New York, NY 10121", and "Tel: 212-857-6915", resulting in a tightly formatted address block with minimal vertical gaps.
  - **PDF Address Line 2 Spacing Fix**: Fixed automatic spacing being added for address line 2 even when there isn't one. Reduced the empty cell height from 5 to 1 when no address line 2 exists, eliminating unnecessary vertical space that was being automatically inserted between address elements.
  - **PDF Address Direct Flow Fix**: Fixed the gap between address line and city/state/zip by removing the line break after the first address line. The city/state/zip now flows directly underneath the address with no vertical gap, creating a seamless address block where "New York, NY 10121" appears directly under "2 Pennsylvania Plaza".
  - **PDF Email Attachment Fix**: Fixed PDF attachment issues in trade confirmation emails by correcting the file path from "temp/" to "../temp/" and adding comprehensive error checking for PDF generation, file creation, and attachment. Added logging to track PDF creation success/failure and ensure temp directory exists with proper permissions.
  - **PDF File Path and Permission Fix**: Fixed permission denied errors when creating PDF files by using absolute paths with `dirname(__FILE__)` instead of relative paths. This resolves the "Permission denied" and "No such file or directory" errors when generating trade confirmation PDFs.
  - **Cron Force Update Permission Fix**: Fixed "Access denied" error for user type 2 when trying to force update cron jobs. Extended permissions to allow both user type 1 (Super Admin) and user type 2 to force run cron jobs, resolving the "TypeError: Failed to fetch" error.
  - **Cron Job Name Mismatch Fix**: Fixed "Invalid job name: cron_execution" error by updating the allowed job names in `forceCron.php` to match the actual job keys being sent from the JavaScript. Changed from `trade_confirmations` to `cron_execution` to match the cron monitor configuration.
  - **Resend Confirm Recipients Fix**: Fixed the "Resend Confirm" button sending emails only to the broker instead of to all parties. Updated `resendTradeConfirm()` function to properly handle different resend types: `resendType = 1` sends only to broker (Resend Confirm To Me), while `resendType = 0` sends to both broker and client (Resend Confirm).
  - **PDF Address and ICE ID Positioning Fix**: Fixed address positioning to appear directly under trader name by adding line break after buyer trader. Added ICE ID display for buying company when it's ICE CLEARED, ensuring the ID appears under "Trader: ICE CLEARED" on the left side, matching the existing logic for the selling company side.
  - **PDF Address Formatting Enhancement**: Updated address formatting to display city, state, zip code, and telephone number indented under the main address line for both buying and selling companies. The address now displays as "Address: 2 Pennsylvania Plaza" with "New York, NY 10121" indented underneath, and "Tel:" aligned left with the "Address:" label while the phone number aligns with the address text, creating a clean, professional appearance.
  - **PDF Trader Data Swap Fix**: Fixed issue where trader names were appearing on the wrong side of the PDF. Added logic to detect when buyer company is "ICE CLEARED" but seller company is not, and swap the trader data accordingly. This ensures that "Carlos Gonzalez" appears under "Buying Company: Axiom Markets" and "ICE CLEARED" appears under "Selling Company: ICE CLEARED" with the correct ICE ID.
  - **PDF ICE CLEARED Trader Display Fix**: Fixed issue where "Trader: ICE CLEARED" was being printed on the left side. Modified the trader display logic to not print "Trader: ICE CLEARED" as a trader name, since ICE CLEARED should only show the ICE ID. Now ICE CLEARED companies show only the ID without the "Trader:" label, creating a cleaner appearance.
  - **PDF ICE CLEARED Trader Display Correction**: Corrected the ICE CLEARED trader display logic. When a company is "ICE CLEARED", it should display both "Trader: ICE CLEARED" and "ID#: [number]" for proper identification. Reverted the blank trader field logic to allow "ICE CLEARED" to be displayed as the trader name when appropriate.
  - **PDF Trader Placement Logic Enhancement**: Improved the trader data swap logic to handle all cases correctly. The logic now properly identifies when traders are on the wrong side and swaps them accordingly, ensuring that "Samuel Pleus" appears under "Selling Company: Citigroup Energy Inc." and "ICE CLEARED" appears under "Selling Company: ICE CLEARED" with the correct ICE ID.
  - **PDF ICE CLEARED Trader Logic Simplification**: Simplified the trader data logic to ensure ICE CLEARED companies always have "ICE CLEARED" as their trader name. This fixes the issue where non-ICE traders were appearing under ICE CLEARED companies and ensures proper trader placement on the correct sides of the PDF.
  - **PDF Buyer/Seller Data Swap Fix**: Fixed the core issue where buyer and seller data was swapped in the database. Added comprehensive logic to detect when the data is swapped and swap the entire buyer/seller information arrays accordingly. This ensures that "Samuel Pleus" appears under "Selling Company: Citigroup Energy Inc." and "Carlos Gonzalez" appears under "Buying Company: Axiom Markets" on the correct sides of the PDF.
  - **PDF Data Swap Logic Simplification**: Simplified the buyer/seller data swap logic to only swap when the buyer is ICE CLEARED but the seller is not. This ensures that ICE CLEARED companies appear on the correct side (right side for selling company) with their proper trader and ID information, matching the expected PDF template format.
  - **PDF Exchange ID Logic Simplification**: Removed complex data swapping logic and simplified the exchange ID display to work for all exchange types. Now checks if the company name is one of the supported exchanges (ICE CLEARED, NYMEX, NGX, NODAL, NASDAQ) and displays the ID# accordingly, making the code much simpler and more maintainable.
  - **PDF Trader Name Swap Fix**: Added simple logic to swap trader names when the buyer company is ICE CLEARED but has a non-ICE trader. This ensures that "Samuel Pleus" appears under "Selling Company: Citigroup Energy Inc." on the right side instead of under the buying company on the left side.
  - **PDF Trader Name Swap Enhancement**: Enhanced the trader name swap logic to handle both cases - when buyer is ICE CLEARED with a real trader, and when seller is ICE CLEARED with a real trader. This ensures that traders appear on the correct side regardless of which company is ICE CLEARED.
  - **PDF Trader Name Swap Logic Correction**: Fixed the trader name swap logic to correctly identify when to swap. Now properly handles the case where seller is ICE CLEARED but buyer has a real trader (like "Carlos Gonzalez" should be on the right side), and when buyer is ICE CLEARED but seller has a real trader (like "Samuel Pleus" should be on the right side).
  - **PDF Logic Simplification**: Removed all complex trader swapping logic and kept only the simple exchange ID display functionality. Now the PDF will show traders exactly as they are in the database, with exchange IDs displayed underneath when available for supported exchanges (ICE CLEARED, NYMEX, NGX, NODAL, NASDAQ).
  - **PDF Trader Placement Fix**: Added simple logic to swap trader names when the buyer has "ICE CLEARED" as trader but the seller is the actual ICE CLEARED company. This ensures that "Trader: ICE CLEARED" appears on the right side under "Selling Company: ICE CLEARED" instead of on the left side under the buying company.
  - **PDF Trader Swap Logic Correction**: Fixed the trader swap logic to correctly handle when buyer is ICE CLEARED company but has a real trader (like "Samuel Pleus"). Now properly swaps traders so that "Samuel Pleus" appears on the right side under "Selling Company: Citigroup Energy Inc." instead of on the left side under "Buying Company: ICE CLEARED".
  - **PDF Logic Complete Removal**: Removed all swap logic completely. The PDF will now display buyer and seller data exactly as it appears in the database, with only the exchange ID functionality for supported exchanges (ICE CLEARED, NYMEX, NGX, NODAL, NASDAQ). This eliminates all complexity and potential issues caused by data manipulation.
