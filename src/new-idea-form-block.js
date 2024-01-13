const { registerBlockType } = wp.blocks;

registerBlockType('wp-roadmap-pro/new-idea-form', {
    title: 'New Idea Form',
    category: 'common',

    edit: () => {
        return (
            <div>
                <p>New Idea Form | This block will display the New Idea Form on the frontend.</p>
            </div>
        );
    },

    save: () => {
        return null; // Rendered with PHP
    },
});
