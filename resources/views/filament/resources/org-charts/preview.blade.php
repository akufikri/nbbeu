<div>
    <style>
        #org-chart-preview { width: 100%; min-height: 20rem; overflow: auto; text-align: center; }
        #org-chart-preview .orgchart { background-image: none; }
        #org-chart-preview .toggleBtn { display: none !important; }
        #org-chart-preview .inactive-node .title,
        #org-chart-preview .inactive-node .content { opacity: 0.45; }
        #org-chart-preview .node .title { background-color: #1c2b4a !important; border-color: #1c2b4a !important; }
        #org-chart-preview .node .content { border-color: #1c2b4a !important; }
        #org-chart-preview .node::before { background-color: #1c2b4a !important; }
        #org-chart-preview .hierarchy::before,
        #org-chart-preview .nodes.vertical .hierarchy::after,
        #org-chart-preview .nodes.vertical .hierarchy::before { border-color: #1c2b4a !important; }
    </style>

    @if (empty($roots))
        <p class="text-sm text-gray-500">No org chart nodes yet.</p>
    @elseif (count($roots) > 1)
        <p class="text-sm text-gray-500">Multiple root nodes found — set "Reports To" on all but one entry to see a single connected chart here.</p>
    @else
        <div
            id="org-chart-preview"
            x-data
            x-init="
                $nextTick(() => {
                    $('#org-chart-preview').orgchart({
                        data: @js($roots[0]),
                        nodeContent: 'title',
                    });
                    $('#org-chart-preview .toggleBtn').remove();
                });
            "
        ></div>
    @endif
</div>
