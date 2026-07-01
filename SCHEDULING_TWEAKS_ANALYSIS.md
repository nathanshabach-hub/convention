# Scheduling Tweaks Analysis - Complete Findings

## Executive Summary

The ACP Live system implements scheduling tweaks through the `Schedulingeventtweaks` database table. However, **only 3 of 5 fields are actively used** during scheduling generation:
- ✅ `pinned_day` - Restricts event to specific day
- ✅ `available_from_time` - Sets minimum start time window
- ✅ `available_to_time` - Sets maximum end time window  
- ❌ `pinned_room_id` - Stored but **NOT used**
- ❌ `pinned_start_time` - Stored but **NOT used**

---

## 1. SCHEDULINGEVENTTWEAKS TABLE STRUCTURE

**Location**: [convention_acp_demo_test.sql](convention_acp_demo_test.sql#L377957)

### Fields:
| Field | Type | Purpose | Used? |
|-------|------|---------|-------|
| `id` | bigint | Primary key | - |
| `conventionseasons_id` | bigint | Convention season FK | Yes |
| `event_id` | bigint | Event FK | Yes |
| `pinned_day` | varchar(32) | Restrict to day of week | ✅ YES |
| `pinned_room_id` | bigint | Lock to room | ❌ NO |
| `pinned_start_time` | time | Fixed start time | ❌ NO |
| `available_from_time` | time | Availability window start | ✅ YES |
| `available_to_time` | time | Availability window end | ✅ YES |
| `created` | datetime | - | - |
| `modified` | datetime | - | - |

---

## 2. WHERE TWEAKS ARE QUERIED

### Primary Location
**File**: [src/Controller/AppController.php](src/Controller/AppController.php)

#### Method: `applySlotConstraintsForConflict()`
**Lines**: [421-505](src/Controller/AppController.php#L421)

This is the **only place** where tweaks are loaded and used in the scheduling algorithm.

```php
// Line 433-435: Query the tweak
$eventTweak = $this->Schedulingeventtweaks->find()->where([
    'Schedulingeventtweaks.conventionseasons_id' => $conventionSD->id,
    'Schedulingeventtweaks.event_id' => $eventId,
])->first();

// Lines 441-445: Extract time constraints
if ($eventTweak) {
    if (!empty($eventTweak->available_from_time)) {
        $eventStart = date('H:i:s', strtotime($eventTweak->available_from_time));
    }
    if (!empty($eventTweak->available_to_time)) {
        $eventEnd = date('H:i:s', strtotime($eventTweak->available_to_time));
    }
}
```

---

## 3. HOW TWEAKS AFFECT SCHEDULING

### A. Pinned Day Constraint
**Lines**: [461-465](src/Controller/AppController.php#L461)

When `pinned_day` is set, the scheduler skips to the next occurrence of that day:

```php
if ($eventTweak && !empty($eventTweak->pinned_day) && 
    date('l', strtotime($candidateDate)) !== $eventTweak->pinned_day) {
    $candidateDate = date('Y-m-d', strtotime($candidateDate . ' +1 day'));
    $candidateStartTime = $normal_starting_time;
    continue;
}
```

**Example**: If Choir is pinned to Wednesday, the scheduler will skip Monday/Tuesday/Thursday/etc.

### B. Event Availability Window
**Lines**: [476-488](src/Controller/AppController.php#L476)

The available time window constrains when an event can be scheduled:

```php
if ($eventStart !== null && 
    strtotime($candidateStartTime) < strtotime($eventStart)) {
    $candidateStartTime = $eventStart;
}

// ... later ...
if ($eventEnd !== null && 
    strtotime($candidateFinishTime) > strtotime($eventEnd)) {
    $candidateDate = date('Y-m-d', strtotime($candidateDate . ' +1 day'));
    $candidateStartTime = $normal_starting_time;
    continue;
}
```

**Example**: `available_from_time = 09:00, available_to_time = 12:00`
- Event cannot start before 09:00
- Event cannot end after 12:00
- If constraint violated, moves to next day

---

## 4. CONSECUTIVE SLOT ALLOCATION (CHOIR & SPELLING)

### Location
**File**: [src/Controller/Admin/SchedulingtimingsController.php](src/Controller/Admin/SchedulingtimingsController.php)

**Primary Code Section**: [Lines 2875-3163](src/Controller/Admin/SchedulingtimingsController.php#L2875) (Category 4 Scheduling)

### Which Events Get Consecutive Slots?

**Automatic Detection** (Line 2875-2878):
```php
$eventNumberNormalized = str_pad((string)ltrim((string)$eventD->event_id_number, '0'), 3, '0', STR_PAD_LEFT);
if (in_array((int)$eventD->id, [3, 11], true) || 
    in_array($eventNumberNormalized, ['003', '053'], true)) {
    $sharedSlotBatchSize = 35;
}
```

**Events Affected**:
- Event IDs: `3`, `11` (Choir events)
- Event ID Numbers: `003`, `053` (Spelling events)
- **Batch Size**: 35 consecutive groups share the same slot

### How Consecutive Slots Work

#### Phase 1: Identify First Slot (Lines 2887-2950)
For the **first** group in the batch:
1. Calculate normal slot: room, day, start_time, finish_time
2. Store as `$sharedSlot`
3. Set `$sharedSlotBatchCount = 1`

#### Phase 2: Reuse Slot for Following Groups (Lines 2883-2906)
For groups **2-35** in the batch:

```php
if ($sharedSlotBatchSize > 1 && !empty($sharedSlot) && 
    $sharedSlotBatchCount < $sharedSlotBatchSize) {
    
    // Reuse the exact same slot
    $this->Schedulingtimings->updateAll([
        'room_id' => $sharedSlot['room_id'],
        'day' => $sharedSlot['day'],
        'start_time' => $sharedSlot['start_time'],
        'finish_time' => $sharedSlot['finish_time'],
        'sch_date_time' => $sharedSlot['date'].' '.date("H:i:s", strtotime($sharedSlot['start_time'])),
        'modified' => date("Y-m-d H:i:s")
    ], ["id" => $schdata->id]);
    
    $sharedSlotBatchCount++;
    $cntrEVSCH++;
    
    if ($sharedSlotBatchCount >= $sharedSlotBatchSize) {
        $sharedSlot = null;  // Reset for next batch
        $sharedSlotBatchCount = 0;
    }
    continue;  // Skip to next group
}
```

#### Phase 3: Create New Slot for Next Batch (Lines 3156-3163)
After group 1 of each batch is scheduled normally:

```php
if ($sharedSlotBatchSize > 1) {
    $sharedSlot = [
        'room_id' => $roomArrCSEvent[$cntrRoomCSEvent],
        'day' => $schDay,
        'start_time' => $start_time,
        'finish_time' => $finish_time,
        'date' => $schStartDate,
    ];
    $sharedSlotBatchCount = 1;
}
```

### Timeline Example

**Choir Event with 5 groups** (sharedSlotBatchSize=35):
```
Group 1: Scheduled normally
  → Result: Wednesday, Room 3, 09:00-09:12
  
Groups 2-5: All reuse Group 1's slot
  → Result: Wednesday, Room 3, 09:00-09:12 (same for all)
```

---

## 5. SCHEDULING CATEGORIES & CONSECUTIVE FLAG

**File**: [src/Template/Element/Admin/Schedulingtimings/viewschedulingc*.ctp](src/Template/Element/Admin/Schedulingtimings/)

### Category 4 (Groups with Consecutive Slots)
**Criteria**: `needs_schedule=1 AND group_event=0 AND Sequential AND has_to_be_consecutive=1`

**Template**: [viewschedulingc4.ctp](src/Template/Element/Admin/Schedulingtimings/viewschedulingc4.ctp#L13)

This category uses the consecutive batch logic described above.

### Events in Sample Data
**File**: [Events List 2026 Small Convention.csv](Events%20List%202026%20Small%20Convention.csv#L135)

```csv
Event ID: 862
Name: Choir  
Event Kind: Sequential
Has To Be Consecutive: TRUE
```

---

## 6. TWEAK MANAGEMENT UI

**File**: [src/Controller/Admin/SchedulingtweaksController.php](src/Controller/Admin/SchedulingtweaksController.php)

### Save/Update Handler
**Method**: `save()` (Lines [111-212](src/Controller/Admin/SchedulingtweaksController.php#L111))

Processes tweaks for individual events.

### Bulk Operations
**Method**: `bulksave()` (Lines [217-420](src/Controller/Admin/SchedulingtweaksController.php#L217))

Applies tweaks to multiple events at once.

### UI Template
**File**: [src/Template/Admin/Schedulingtweaks/index.ctp](src/Template/Admin/Schedulingtweaks/index.ctp)

- **Line 106**: Display tweak status
- **Line 235-242**: Show current tweak values (pinned_day, pinned_room, pinned_start_time)
- **Line 264-266**: Store data attributes with current values
- **Line 288-330**: Forms for editing tweaks

---

## 7. KEY FINDINGS

### ✅ Working Features
1. **Pinned Day**: Fully functional - restricts scheduling to specific day
2. **Availability Window**: Fully functional - constrains scheduling times
3. **Consecutive Batches**: Fully functional - Choir/Spelling get 35 groups per slot

### ❌ Non-Functional Features
1. **Pinned Room**: Stored in DB but **never used** during scheduling
   - Admins can set it, but scheduler ignores it
   - Room assignment is automatic, not constrained by this field

2. **Pinned Start Time**: Stored in DB but **never used** during scheduling
   - Admins can set it, but scheduler ignores it
   - Start time is calculated by algorithm, not fixed by this field

### Consecutive Slot Duration
- **Default**: 12 minutes (eventSetupRoundJudTime)
- **Calculated**: `finish_time = start_time + 12 minutes`
- **Example**: 09:00-09:12, 09:13-09:25, 09:26-09:38...

### Wednesday 9:00 AM Handling
❌ **NOT HARDCODED** in system

To achieve this, would need:
1. Create tweak with `pinned_day = 'Wednesday'`
2. Set `available_from_time = '09:00:00'`
3. Set `available_to_time = '09:12:00'` (or later)

**BUT**: This still won't guarantee Wednesday 9:00 for Choir because:
- `pinned_room_id` is ignored (so room isn't locked)
- Consecutive batches work, but first group can be scheduled anytime in the window
- If 35 groups can't fit in the window, the algorithm will find the next available slot

---

## 8. SCHEDULING GENERATION FLOW

**Entry Points**:
- [startschedulec1()](src/Controller/Admin/SchedulingtimingsController.php#L630) - Category 1
- [startschedulec2()](src/Controller/Admin/SchedulingtimingsController.php) - Category 2
- [startschedulec3()](src/Controller/Admin/SchedulingtimingsController.php) - Category 3
- [startschedulec4()](src/Controller/Admin/SchedulingtimingsController.php) - Category 4

**Constraint Application**:
1. Main scheduling loop builds initial slots
2. When conflicts occur, `applySlotConstraintsForConflict()` is called
3. Tweaks are checked:
   - If pinned_day doesn't match → skip to next day
   - If outside availability window → skip to next day
4. Consecutive batches reuse slots automatically

---

## 9. MODEL ASSOCIATIONS

**Model File**: [src/Model/Table/SchedulingeventtweaksTable.php](src/Model/Table/SchedulingeventtweaksTable.php) (if exists)

**Relationships**:
- FK: `conventionseasons_id` → Conventionseasons
- FK: `event_id` → Events

---

## 10. CONCLUSION & RECOMMENDATIONS

### Current State
- Tweaks system is **partially implemented**
- Only 60% of fields are functional
- Consecutive allocation works well for Choir/Spelling
- Time window constraints work well

### For Wednesday 9:00 AM Requirement
To fully support pinned room + pinned start time:

**Option 1**: Implement missing tweak logic
- Modify [applySlotConstraintsForConflict()](src/Controller/AppController.php#L421)
- Add code to check and enforce `pinned_room_id`
- Add code to check and enforce `pinned_start_time`

**Option 2**: Use alternative constraints
- Rely only on `pinned_day` + `available_from_time` + `available_to_time`
- Create window: Wednesday 09:00-09:12 (for single Choir slot)
- First group will fit there; subsequent groups will reuse it

---

## DETAILED CODE REFERENCES

| Aspect | File | Line Range | Function |
|--------|------|-----------|----------|
| Tweak Query | AppController.php | 433-435 | applySlotConstraintsForConflict() |
| Pinned Day Logic | AppController.php | 461-465 | applySlotConstraintsForConflict() |
| Available Window | AppController.php | 441-445, 476-488 | applySlotConstraintsForConflict() |
| Consecutive Batch Init | SchedulingtimingsController.php | 2875-2878 | startschedulec4() |
| Consecutive Batch Reuse | SchedulingtimingsController.php | 2883-2906 | startschedulec4() |
| Save Slot Template | SchedulingtimingsController.php | 3156-3163 | startschedulec4() |
| Tweak Management UI | SchedulingtweaksController.php | 35-212 | initialize(), save() |
| Tweak Display | Schedulingtweaks/index.ctp | 235-330 | Template |

---

**Analysis Date**: 2026-06-25
**System**: ACP Live - Convention Management System
**Database**: convention_acp_demo_test
