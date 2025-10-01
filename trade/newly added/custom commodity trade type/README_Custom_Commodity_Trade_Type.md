# 🆕 Custom Commodity Trade Type (Type 9)

## Overview
This adds a new trade type called "Custom Commodity" to your Greenlight Commodities trading system. This trade type allows users to create trades with custom fields for new products, locations, offset indices, regions, and other commodity-specific information.

## 🗄️ Database Setup

### 1. Run the SQL Script
Execute the `add_custom_commodity_trade_type.sql` file in your database to:
- Add the new trade type (ID: 9)
- Create the template configuration
- Create the custom fields table

### 2. Database Tables Created
- **`tblB2TCustomCommodityFields`** - Stores the custom commodity data
- **Template entry in `tblB2TTemplates`** - Controls which fields are visible

## 🎯 New Fields Available

When you select "Custom Commodity" from the trade type dropdown, you'll see these additional fields:

### **New Product Fields:**
- **New Product** - Text input for custom product names
- **New Location** - Text input for custom delivery locations  
- **New Offset Index** - Text input for custom offset indices
- **New Region** - Text input for custom regions

### **Commodity Classification:**
- **Commodity Type** - Dropdown with options:
  - Agricultural
  - Energy
  - Metals
  - Softs
  - Other

### **Advanced Fields:**
- **Pricing Model** - Textarea for describing pricing structures
- **Settlement Terms** - Textarea for settlement conditions

## 🚀 How to Use

### 1. Create a New Trade
1. Go to Dashboard (`index.php?p=dashboard`)
2. In "Create New Trade" section, select **"Custom Commodity"** from dropdown
3. Click **"Create Trade"** button
4. Fill in the standard trade fields (buyer, seller, dates, etc.)
5. Fill in the new custom commodity fields
6. Submit the trade

### 2. URL Access
You can also access directly via:
```
trade/tradeInput.php?tradeType=9&height=570
```

## 🔧 Technical Implementation

### Files Modified:
- `trade/trade/tradeInput.php` - Added custom field display and form handling
- `trade/functions.php` - Added `populateCustomCommodityFields()` and `updateCustomCommodityFields()` functions

### Functions Added:
- **`populateCustomCommodityFields($postData, $transRefID)`** - Inserts new custom commodity data
- **`updateCustomCommodityFields($postData, $updateID)`** - Updates existing custom commodity data

### Database Integration:
- Custom fields are automatically saved when creating/updating trades
- Data is linked to the main transaction via foreign key
- Supports both new trades and trade updates

## 🎨 Customization Options

### Adding More Fields:
To add additional custom fields:

1. **Add to the form** in `tradeInput.php`:
```php
<tr>
    <td class="fieldLabel">New Field: </td>
    <td><input type="text" name="newField" id="newField" value="<?php echo($newField); ?>"></td>
</tr>
```

2. **Add to the database table**:
```sql
ALTER TABLE tblB2TCustomCommodityFields ADD COLUMN fldNewField VARCHAR(255);
```

3. **Update the functions** in `functions.php` to handle the new field

### Modifying Field Types:
- Change `VARCHAR(255)` to `TEXT` for longer text
- Use `ENUM()` for dropdown options
- Add `DECIMAL(10,2)` for numeric values
- Use `DATE` for date fields

## 🧪 Testing

### 1. Verify Database Setup
```sql
SELECT * FROM tblB2TTradeType WHERE pkTypeID = 9;
SELECT * FROM tblB2TTemplates WHERE fkTypeID = 9;
DESCRIBE tblB2TCustomCommodityFields;
```

### 2. Test Trade Creation
1. Create a trade with type 9
2. Fill in custom fields
3. Submit and verify data is saved
4. Check the custom fields table for the new record

### 3. Test Trade Updates
1. Edit an existing type 9 trade
2. Modify custom fields
3. Verify changes are saved

## 🚨 Troubleshooting

### Common Issues:
- **Fields not showing**: Check if trade type 9 exists in database
- **Data not saving**: Verify custom fields table was created
- **PHP errors**: Check if functions were added to functions.php

### Debug Steps:
1. Check browser console for JavaScript errors
2. Verify database table structure
3. Check PHP error logs
4. Confirm trade type ID matches in all references

## 📝 Going Forward

### Better Wording for Future Requests:
Instead of "I need to create another one with different fields", try:

- **"I need to add a new trade type with custom commodity fields"**
- **"I want to create a trade template for renewable energy products"**
- **"I need to add specialized fields for agricultural commodity trades"**
- **"I want to create a custom trade form for metals trading"**

### What to Specify:
- **Trade Type Name**: What should it be called?
- **Field Types**: Text, dropdown, textarea, date, number?
- **Field Validation**: Required fields, format restrictions?
- **Business Logic**: Any calculations or dependencies?
- **Integration**: How should it work with existing systems?

## 🤝 Support

If you need help with:
- Adding more custom fields
- Modifying field behavior
- Creating additional trade types
- Database schema changes

Just describe what you want to achieve, and I can help you implement it step by step!
