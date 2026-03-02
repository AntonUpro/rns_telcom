# PlatformSectionManager Component

## Overview
Component for managing platform/structure sections with detailed element configuration. Allows adding/removing sections and configuring construction elements within each section.

## Features
- **Section Management**: Add/remove sections anywhere in the list
- **Section Configuration**: Set height, bottom width, and top width for each section
- **Element Management**: Add/remove construction elements within sections
- **Element Types**: Support for belts, braces, struts, sprengeles, and other elements
- **Cross-section Types**: Angle bars, pipes, channels, double angles, square pipes, etc.
- **Profile Search**: Integration-ready for backend profile database search
- **Responsive Design**: Works on desktop and mobile devices

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `modelValue` | Array | `[]` | Array of section objects |
| `editable` | Boolean | `true` | Whether the component is in edit mode |

## Data Structure

### Section Object
```javascript
{
    id: 'unique-id',
    height: 3.5,           // Height in meters
    bottomWidth: 2.0,      // Bottom width in meters
    topWidth: 1.8,         // Top width in meters
    elements: [...]        // Array of element objects
}
```

### Element Object
```javascript
{
    id: 'unique-id',
    type: 'belt',          // 'belt' | 'brace' | 'strut' | 'sprengel' | 'other'
    crossSection: 'angle', // 'angle' | 'pipe' | 'channel' | 'double_angle' | 'square_pipe' | 'other'
    profileSize: '',       // Selected profile size
    profileSearchTerm: ''  // Current search term
}
```

## Events
- `update:modelValue` - Emitted when sections data changes

## Usage Example

```vue
<template>
    <PlatformSectionManager 
        v-model="sections"
        :editable="true"
    />
</template>

<script setup>
import { ref } from 'vue';
import PlatformSectionManager from './PlatformSectionManager.vue';

const sections = ref([
    {
        id: 'section-1',
        height: 3.5,
        bottomWidth: 2.0,
        topWidth: 1.8,
        elements: [
            {
                id: 'element-1',
                type: 'belt',
                crossSection: 'angle',
                profileSize: '100x100x10',
                profileSearchTerm: '100x100x10'
            }
        ]
    }
]);
</script>
```

## Backend Integration

The component is ready for backend integration for profile search functionality. The `handleProfileSearch` method currently logs search terms but can be easily connected to an API endpoint.

## Styling

The component uses scoped CSS and follows the existing application design system. Key style classes:
- `.platform-section-manager` - Main container
- `.section-card` - Individual section containers
- `.elements-table` - Elements table styling
- Responsive breakpoints at 768px and 480px

## Future Enhancements
- Profile database integration with real-time search
- Drag-and-drop reordering of sections and elements
- Bulk import/export functionality
- Advanced validation rules
- Custom element types configuration