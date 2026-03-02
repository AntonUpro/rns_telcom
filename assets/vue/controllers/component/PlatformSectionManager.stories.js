// Storybook-like examples for PlatformSectionManager component

export default {
    title: 'Components/PlatformSectionManager',
    component: PlatformSectionManager,
};

// Basic usage story
export const Default = {
    args: {
        modelValue: [],
        editable: true
    }
};

// With sample data
export const WithSampleData = {
    args: {
        modelValue: [
            {
                id: 'sec1',
                height: 3.5,
                bottomWidth: 2.0,
                topWidth: 1.8,
                elements: [
                    {
                        id: 'elem1',
                        type: 'belt',
                        crossSection: 'angle',
                        profileSize: '100x100x10',
                        profileSearchTerm: '100x100x10'
                    },
                    {
                        id: 'elem2',
                        type: 'brace',
                        crossSection: 'pipe',
                        profileSize: 'Φ89x4',
                        profileSearchTerm: 'Φ89x4'
                    }
                ]
            },
            {
                id: 'sec2',
                height: 2.8,
                bottomWidth: 1.8,
                topWidth: 1.6,
                elements: [
                    {
                        id: 'elem3',
                        type: 'sprengel',
                        crossSection: 'channel',
                        profileSize: '[20',
                        profileSearchTerm: '[20'
                    }
                ]
            }
        ],
        editable: true
    }
};

// Read-only mode
export const ReadOnly = {
    args: {
        modelValue: [
            {
                id: 'sec1',
                height: 4.2,
                bottomWidth: 2.5,
                topWidth: 2.2,
                elements: [
                    {
                        id: 'elem1',
                        type: 'belt',
                        crossSection: 'double_angle',
                        profileSize: '2L125x10',
                        profileSearchTerm: '2L125x10'
                    }
                ]
            }
        ],
        editable: false
    }
};