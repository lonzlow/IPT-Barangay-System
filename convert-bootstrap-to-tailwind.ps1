# Bulk Bootstrap to Tailwind conversion script
$files = @(
    "resources/views/documents.blade.php",
    "resources/views/blotter.blade.php",
    "resources/views/households.blade.php",
    "resources/views/business.blade.php",
    "resources/views/committees.blade.php",
    "resources/views/officials.blade.php",
    "resources/views/reports.blade.php",
    "resources/views/users.blade.php"
)

foreach ($file in $files) {
    if (Test-Path $file) {
        Write-Host "Converting $file..."
        $content = Get-Content $file -Raw
        
        # Common replacements
        $replacements = @(
            # Bootstrap CSS CDN removal - already done in layout
            
            # Grid classes
            (@'
<div class="row g-3
'@, @'
<div class="grid grid-cols-1 gap-3 lg:grid-cols-
'@),
            
            (@'
col-6 col-xl-3
'@, @'
col-span-2 lg:col-span-1
'@),
            
            (@'
col-6 col-md-3
'@, @'
col-span-2 lg:col-span-1
'@),
            
            (@'
col-md-4
'@, @'
lg:col-span-1
'@),
            
            (@'
col-md-5
'@, @'
lg:col-span-2
'@),
            
            (@'
col-md-7
'@, @'
lg:col-span-2
'@),
            
            (@'
col-md-3 col-xl-4
'@, @'
lg:col-span-1
'@),
            
            (@'
col-lg-4
'@, @'
lg:col-span-1
'@),
            
            (@'
col-lg-5
'@, @'
lg:col-span-2
'@),
            
            (@'
col-lg-7
'@, @'
lg:col-span-3
'@),
            
            (@'
col-lg-8
'@, @'
lg:col-span-2
'@),
            
            # Flex utilities
            (@'
d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4
'@, @'
flex items-center justify-between flex-wrap gap-3 mb-4
'@),
            
            (@'
d-flex align-items-center gap-
'@, @'
flex items-center gap-
'@),
            
            (@'
d-flex flex-column
'@, @'
flex flex-col
'@),
            
            (@'
ms-auto
'@, @'
ml-auto
'@),
            
            (@'
me-
'@, @'
mr-
'@),
            
            (@'
mb-
'@, @'
mb-
'@),
            
            (@'
mt-
'@, @'
mt-
'@),
            
            (@'
text-center
'@, @'
text-center
'@),
            
            # Button classes
            (@'
btn btn-primary
'@, @'
px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition-colors
'@),
            
            (@'
btn btn-sm btn-light
'@, @'
px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg text-xs transition-colors
'@),
            
            (@'
btn btn-outline-secondary
'@, @'
px-3 py-1.5 border border-gray-300 rounded-lg bg-white hover:bg-gray-50 text-gray-600 text-xs font-medium transition-colors
'@),
            
            # Form classes  
            (@'
form-control
'@, @'
px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white
'@),
            
            (@'
form-select
'@, @'
px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white
'@),
            
            # Badge classes
            (@'
badge bg-light text-secondary
'@, @'
inline-block px-2.5 py-1 text-xs font-semibold bg-gray-100 text-gray-700 rounded
'@),
            
            (@'
badge ms-
'@, @'
inline-block ml-
'@)
        )
        
        foreach ($replacement in $replacements) {
            $content = $content -replace [regex]::Escape($replacement[0]), $replacement[1]
        }
        
       Set-Content $file $content -Encoding UTF8
        Write-Host "✓ Completed $file"
    } else {
        Write-Host "✗ File not found: $file"
    }
}

Write-Host "`nConversion complete!"
