# 🚀 Greenlight Commodities - Quick Reference Changelog

### **🧹 What Was Cleaned Up (v2.8.20)**
- ✅ **URL Parameters** - Removed unused `height=570` from all trade popup URLs
- ✅ **Cleaner URLs** - Popup URLs now only contain necessary parameters
- ✅ **Better Maintenance** - No more confusion between URL params and actual window size
- **Files**: `scripts.js`

### **🎯 What Was Enhanced (v2.8.19)**
- ✅ **Popup Window Sizes** - Increased from 615×900 to 900×1200 pixels for all trade popups
- ✅ **Better Form Layout** - More comfortable spacing and reduced scrolling
- ✅ **Consistent Sizing** - All trade creation/editing popups now use uniform larger dimensions
- **Files**: `scripts.js`

### **🐛 What Was Fixed (v2.8.18)**
- ✅ **Calendar Positioning** - Calendars now appear near calendar buttons instead of at page top
- ✅ **Dynamic Placement** - JavaScript positions calendars relative to clicked buttons
- ✅ **Better User Experience** - No more scrolling to find calendar popups
- **Files**: `tradeInput.php`

### **🐛 What Was Fixed (v2.8.17)**
- ✅ **Calendar Z-Index** - Increased to 99999 for proper layering above all elements
- ✅ **Trade Filters Overlap** - Calendars no longer hidden behind Trade Filters panel
- ✅ **Proper Stacking** - All calendar elements now appear on top of page content
- **Files**: `styles.css`, `styles-dark.css`

### **🐛 What Was Fixed (v2.8.10)**
- ✅ **Calendar CSS Restored** - Removed all custom CSS interfering with YUI library
- ✅ **Working Test Version Match** - Now uses identical code as working test version
- ✅ **Natural YUI Behavior** - YUI library handles close buttons naturally without interference
- **Files**: `styles.css`, `styles-dark.css`, `dashboard.php`

### **🐛 What Was Fixed (v2.8.9)**
- ✅ **Calendar Close Button CSS** - Removed all custom CSS interfering with YUI library
- ✅ **Natural YUI Behavior** - Now using library's default close button behavior
- ✅ **Simplified Approach** - No more complex CSS overrides fighting the library
- **Files**: `styles.css`, `styles-dark.css`

### **🐛 What Was Fixed (v2.8.8)**
- ✅ **Calendar Configuration** - Fixed calendar config to match working test version
- ✅ **Close Button Parameter** - Removed conflicting `closeButton:true` parameter
- ✅ **Simple Configuration** - Now using only `close:true` like working version
- **Files**: `tradeInput.php`

### **🐛 What Was Fixed (v2.8.7)**
- ✅ **Close Button X Symbol** - YUI calendar close buttons now show X symbol (✕) instead of text
- ✅ **Proper YUI Integration** - Working with library instead of fighting it
- ✅ **Professional Appearance** - Clean X symbol with proper styling in both themes
- **Files**: `styles.css`, `styles-dark.css`, all calendar files

### **🆕 What's New (v2.8.6)**
- ✅ **System-Wide Calendar Close Buttons** - All calendars throughout the system now have close buttons
- ✅ **Consistent User Experience** - Identical close button behavior across all pages
- ✅ **Professional Interface** - All calendars now have consistent, professional close buttons
- **Files**: `dashboard.php`, `indices.php`, `invoice.php`, `reports.php`, `confirm_hub.php`

### **🐛 What Was Fixed (v2.8.4)**
- ✅ **Calendar Close Button Styling** - All calendars now have proper `class="close-icon calclose"` styling
- ✅ **End Date Layout Fixed** - End Date calendar no longer disrupts form layout
- ✅ **Professional Appearance** - Close buttons match design with white background and gray border
- **Files**: `tradeInput.php`, `styles.css`, `styles-dark.css`

### **🐛 What Was Fixed (v2.8.3)**
- ✅ **Calendar Close Buttons** - All trade input calendars now have visible close buttons (X)
- ✅ **Layout Issues Fixed** - End Date calendar no longer disrupts form layout
- ✅ **Calendar Icon Cursors** - All calendar icons show pointer cursor on hover
- **Files**: `tradeInput.php`, `styles.css`, `styles-dark.css`

### **🐛 What Was Fixed (v2.8.2)**
- ✅ **Dashboard Calendar Buttons** - Calendar buttons now work when clicked on dashboard page
- ✅ **Date Selection** - Users can now properly select dates from dashboard trade filters
- **Files**: `dashboard.php`

### **�� What Was Fixed (v2.8.1)**
- ✅ **Calendar Auto-Opening Issue** - YUI calendars no longer automatically open when trade form loads
- ✅ **Multiple Calendar Display** - Only one calendar visible at a time, not all three simultaneously
- ✅ **Clean Form Loading** - Trade input form now loads without overlapping calendar popups
- **Files**: `tradeInput.php`, `styles.css`, `styles-dark.css`

### **🆕 What's New (v2.8.0)**
- ✅ **User Type Testing Tool** - Administrators can switch between user types for testing
- ✅ **No Logout Required** - Test different permission levels instantly
- ✅ **Temporary Override** - System temporarily applies selected user type permissions
- ✅ **Reset Functionality** - Easy return to actual administrator permissions

### **🆕 What's New (v2.7.0)**
- ✅ **Missing Fields Fixed** - Location, Region, Offset Index, Unit Frequency now visible
- ✅ **Complete Form** - New Product trades now have all same fields as Natural Gas
- ✅ **Auto-Population** - Location → Region now works for New Product trades
- ✅ **Product Dropdown** - No more static "Heat Rate" text, proper dropdown now

### **🆕 What's New (v2.6.0)**
- ✅ **Simplified Interface** - Removed all duplicate custom fields
- ✅ **Standard Fields** - New Product trades now use same fields as Natural Gas
- ✅ **Clean Code** - Removed ~160 lines of unnecessary custom field logic
- ✅ **Auto-Population** - Location → Region works exactly like other trade types

### **🆕 What's New (v2.5.0)**
- ✅ **Checkbox Fields** - Pricing Model and Settlement Terms now use checkboxes
- ✅ **Smart Text Areas** - Only enabled when checkboxes are checked
- ✅ **Buyer Price Field** - Always visible for New Product trades
- ✅ **Buyer Currency Field** - Always visible with proper currency selection

### **🆕 What's New (v2.4.0)**
- ✅ **Duplicate Fields Removed** - no more conflicting Location/Region fields
- ✅ **Region Auto-Update** - Region dropdown automatically populates when Location changes
- ✅ **Clean Interface** - New Product trades now show only custom dropdown fields
- ✅ **Smart Region Detection** - automatic region selection based on location type

### **🆕 What's New (v2.3.0)**
- ✅ **PDF Confirmations** now include all custom commodity fields
- ✅ **Excel Invoices** properly display custom commodity data
- ✅ **Complete Data Integration** across all output formats
- ✅ **Professional Formatting** for all custom fields

### **🆕 What's New (v2.2.0)**
- ✅ **Custom Fields as Dropdowns** - no more free-text input
- ✅ **Data Consistency** - selections from existing database
- ✅ **Dynamic Region Updates** - location-dependent region population
- ✅ **Professional UI** - follows same pattern as other trade types

### **🆕 What's New (v2.1.0)**
- ✅ **New Product 1 & 2** trade types added to Create Trade dropdown
- ✅ **MMBTU/Block selector** replaces static MMBTU text
- ✅ **8 Custom fields** for new product specifications
- ✅ **Dynamic ID system** - no more hardcoded trade type numbers

### **🔧 What Was Fixed**
- ✅ **Calendar popups** now work properly
- ✅ **Database compatibility** - SQL scripts match your exact structure
- ✅ **Code architecture** - future-proof and flexible
- ✅ **Data Flow** - custom fields appear everywhere they should
- ✅ **Duplicate Fields** - removed conflicting Location/Region inputs
- ✅ **Region Updates** - dropdown now automatically populates
- ✅ **Checkbox Logic** - only save content when fields are enabled
- ✅ **Interface Simplification** - removed all duplicate custom fields
- ✅ **Standard Behavior** - Location → Region works like Natural Gas trades
- ✅ **Missing Fields** - Location, Region, Offset Index, Unit Frequency now visible
- ✅ **Product Field** - Proper dropdown instead of static "Heat Rate" text

### **📁 What Was Organized**
- ✅ **File structure** organized in `trade/newly added/` folder
- ✅ **Documentation** - complete setup guides and READMEs
- ✅ **Backup system** - automated database backup scripts

---

## 🎯 **Current Status**

### **✅ Working Features**
- New Product 1 & 2 trade types
- MMBTU/Block dropdown selector
- **Complete standard fields** (Product, Product Type, Location, Offset Index, Region, Unit Frequency)
- **Location → Region auto-population** (like Natural Gas trades)
- Calendar functionality
- Dark mode toggle
- **PDF Confirmations with custom fields**
- **Excel Invoices with custom fields**
- **Clean interface with no duplicate fields**
- **Checkbox-enabled Pricing Model & Settlement Terms**
- **Always-visible Buyer Price & Currency fields**

### **🔄 In Progress**
- Testing complete data flow integration
- Verifying all output formats display correctly

### **📋 Next Steps**
1. Test new trade types end-to-end
2. Verify PDF confirmations show custom fields
3. Check Excel invoices include custom data
4. Confirm data consistency across all formats
5. Test Location → Region auto-update functionality
6. Test checkbox functionality for Pricing Model & Settlement Terms
7. **Verify standard field behavior matches Natural Gas trades**
8. **Test all missing fields are now visible and functional**

---

## 🚨 **Important Notes**

- **Version**: Currently at v2.7.0
- **Database**: SQL changes applied successfully
- **Code**: PHP files updated for complete integration
- **Testing**: Ready for comprehensive testing
- **Output**: All formats now support custom commodity fields
- **Interface**: Clean, professional UI with no duplicate fields
- **Fields**: Checkbox-enabled optional fields with smart saving
- **Behavior**: Standard fields work exactly like Natural Gas trades
- **Completeness**: All essential trade fields now visible for New Product trades

---

## 📞 **Need Help?**

- **Changelog**: See `CHANGELOG.md` for complete history
- **Documentation**: Check `custom commodity trade type/` folder
- **Backup**: See `automated backup/` folder

---

*Quick reference for the latest changes. Full details in `CHANGELOG.md`*
