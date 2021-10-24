const cTel = requestForm.querySelectorAll('input[type="tel"]')
Array.from(cTel).forEach((tel) => {
 IMask(
  tel,
  {mask: "+{7}(000)000-00-00"}
 )
})