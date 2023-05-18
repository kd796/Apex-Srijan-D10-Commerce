export const formatAddress = (fields) => {
  let address = '';
  address += fields.address_line1;
  if (fields.address_line2) {
    address += '<br>' + fields.address_line2;
  }

  if (fields.locality || fields.administrative_area || fields.postal_code) {
    address += '<br>';
    let cityState = [fields.locality, fields.administrative_area].filter(item => item).join(', ');
    let zip = fields.postal_code;
    address += [cityState, zip].filter(item => item).join(" ");
  }

  if (fields.country_code) {
    address += "<br>" + fields.country_code;
  }

  return address;
};

export default formatAddress;
