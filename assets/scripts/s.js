const cTel = document.querySelectorAll('input[type="tel"]')
Array.from(cTel).forEach((tel) => {
 IMask(
  tel,
  {mask: "+{7}(000)000-00-00"}
 )
})

/*
axios({
 method: 'post',
 url: 'https://',
 data: {

 }
})
.then((response) => { console.log('response.data', response.data)

})
.catch((error) => console.log(error))
*/