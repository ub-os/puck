// import fs 
import fs from 'fs';
// import path from 'path';



/////////////////////////////////////////////////////////////
//  script to generate the pie icons for container_width   //
//  useful if you want to change the grid layout           //
//  improvements: use node path module                     //
/////////////////////////////////////////////////////////////


// get the grid size from the defaults defined at "$grid-columns"
const defaults = fs.readFileSync('puck/Resources/Private/Stylesheets/00-settings/_default.sass', 'utf8');



// match '$grid-columns:' and get the next number behind the string until a '!' is found
const cols = defaults.match(/\$grid-columns:\s*([\d\.]+[a-zA-Z%]*)/);

// Initialize variables
const n = parseInt(cols[1] ? cols[1] : 12); // Number of files to create
const svgWidth = 32; // SVG width
const svgHeight = 32; // SVG height
const radius = 15; // The radius of the circle
const cx = svgWidth / 2; // x-coordinate of the circle's center
const cy = svgHeight / 2; // y-coordinate of the circle's center

for(let m = 1; m <= n; m++) {
  // Calculate angle for pie cut
  const angle = (m / n) * 360;

  // Create SVG string
  let svgString = `<?xml version="1.0" encoding="UTF-8"?>`;
  svgString += `<svg height="${svgHeight}" width="${svgWidth}" viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">`;

  // setting circles color to full yellow because full pie cut demonstration is weird
  svgString += `<circle cx="${cx}" cy="${cy}" r="${radius}" stroke="${m === n ? '#FBAA04' : '#515151'}" stroke-width="1" fill="${m === n ? '#FBAA04' : '#515151'}" />`;
  
  // Add pie cut
  const x2 = cx + radius * Math.sin(angle * Math.PI / 180);
  const y2 = cy - radius * Math.cos(angle * Math.PI / 180);
  svgString += `<path d="M ${cx},${cy} L ${cx},${cy - radius} A ${radius},${radius} 0 ${angle > 180 ? 1 : 0},1 ${x2},${y2} Z" stroke="#FBAA04" stroke-width="1" fill="#FBAA04"/>`;

  svgString += '</svg>';

  // Write to Backend Icons directory
  fs.writeFileSync(`puck/Resources/Public/Icons/Backend/ColumnWidth${m}.svg`, svgString);
}
