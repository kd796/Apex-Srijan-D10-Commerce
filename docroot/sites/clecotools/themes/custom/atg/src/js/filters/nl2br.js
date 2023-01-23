export const nl2br = function (string) {
  string = string.toString();
  string = string.replace(/[\n|\r]/g, "<br>");
  // console.log(string);
  return string;
};

export default nl2br;
