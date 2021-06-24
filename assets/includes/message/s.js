const asideMessage = document.createElement('aside')
asideMessage.setAttribute('id', 'message')
asideMessage.addEventListener('click', closeMessage)


document.addEventListener('DOMContentLoaded', () => {
 document.querySelector('body').prepend(asideMessage)
})

const aSTMessage = []
const delayMessage = 6000

function openMessage(message = '?', type = 0){
 if(asideMessage.classList.contains('active')){
  (aSTMessage[message]) && clearTimeout(aSTMessage[message])
  aSTMessage[message] = setTimeout(() => {
   openMessage(message, type)
  }, delayMessage)

  return false
 } //console.log('openMessage');

 asideMessage.textContent = message

 if(type === 1){
  asideMessage.style.backgroundColor = 'rgba(0, 200, 0, 0.8)'
 } else if(type === 2){
  asideMessage.style.backgroundColor = 'rgba(200, 0, 0, 0.8)'
  asideMessage.style.color = 'white'
 } else{ //default
  asideMessage.style.backgroundColor = 'rgba(255, 200, 20, 0.8)'
 }

 asideMessage.classList.add('active')

 aSTMessage['close message'] = setTimeout(closeMessage, delayMessage)
}


function closeMessage(){
 if(!asideMessage.classList.contains('active')){
  return false
 } //console.log('closeMessage');

 (aSTMessage['close message']) && clearTimeout(aSTMessage['close message'])

 asideMessage.classList.remove('active')

 setTimeout(() => {
  asideMessage.textContent = ''
   asideMessage.removeAttribute('style')
 }, 400)
}