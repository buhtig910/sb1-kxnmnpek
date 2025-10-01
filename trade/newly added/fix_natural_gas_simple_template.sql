-- Comprehensive fix for Natural Gas Simple template
-- This ensures all fields are properly configured

-- First, let's see the current template
SELECT 'Current Template:' as info;
SELECT * FROM tblB2TTemplates WHERE fkTypeID = 10;

-- Update the template to match what we want for Natural Gas Simple
-- Based on the template structure, we need to ensure Unit Frequency is enabled
UPDATE tblB2TTemplates 
SET 
    fldUnitFrequency = 1,  -- Enable Unit Frequency
    fldOffsetIndex = 1,    -- Enable Offset Index
    fldDeliveryType = 1,   -- Enable Delivery Type
    fldBuyerPrice = 1,     -- Enable Buyer Price
    fldBuyerCurrency = 1,  -- Enable Buyer Currency
    fldSellerPrice = 1,    -- Enable Seller Price
    fldSellerCurrency = 1, -- Enable Seller Currency
    fldOptionStyle = 0,    -- Disable Option Style
    fldOptionType = 0,     -- Disable Option Type
    fldExerciseType = 0,   -- Disable Exercise Type
    fldStrike = 0,         -- Disable Strike
    fldStrikeCurrency = 0, -- Disable Strike Currency
    fldBlock = 1,          -- Enable Block
    fldHours = 1,          -- Enable Hours
    fldTimeZone = 1,       -- Enable Timezone
    fldIndexPrice = 1,     -- Enable Index Price
    fldCustomFields = 0    -- Disable Custom Fields
WHERE fkTypeID = 10;

-- Verify the update
SELECT 'Updated Template:' as info;
SELECT * FROM tblB2TTemplates WHERE fkTypeID = 10;

-- Compare with original Natural Gas Option trade type (ID 6)
SELECT 'Original Natural Gas Option Template for comparison:' as info;
SELECT * FROM tblB2TTemplates WHERE fkTypeID = 6;
