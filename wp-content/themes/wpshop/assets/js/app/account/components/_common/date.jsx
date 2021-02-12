function prettyDate(dateString) {
  var options = { year: 'numeric', month: 'long', day: 'numeric' };
  var dateOb = new Date(dateString);

  return dateOb.toLocaleDateString('en-US', options);
}

export default prettyDate;
