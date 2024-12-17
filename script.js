// Função para alternar entre as seções
function showSection(sectionId) {
    // Esconde todas as seções
    document.querySelectorAll('section').forEach(section => {
        section.classList.remove('active');
    });
    
    // Mostra a seção selecionada
    const selectedSection = document.getElementById(sectionId);
    selectedSection.classList.add('active');
    selectedSection.style.opacity = 0;

    // Animação de transição
    setTimeout(() => {
        selectedSection.style.opacity = 1;
    }, 50);
}

// Função para visualizar detalhes do time
function viewTeamDetails(teamId) {
    alert(`Detalhes do time ${teamId} serão implementados em breve!`);
}

// Dados mockados para exemplo
const mockData = {
    teams: [
        {
            name: 'Grifinória',
            wins: 10,
            losses: 2,
            players: ['Harry Potter', 'Ron Weasley', 'Katie Bell']
        },
        {
            name: 'Sonserina',
            wins: 8,
            losses: 4,
            players: ['Draco Malfoy', 'Marcus Flint', 'Adrian Pucey']
        }
    ],
    players: [
        {
            name: 'Harry Potter',
            position: 'Apanhador',
            team: 'Grifinória',
            stats: {
                agility: 95,
                strength: 75,
                speed: 90
            }
        }
    ],
    matches: [
        {
            team1: 'Grifinória',
            team2: 'Sonserina',
            date: '15/12/2023',
            status: 'scheduled'
        }
    ]
};

// Inicializa a página mostrando a seção de times
document.addEventListener('DOMContentLoaded', () => {
    showSection('teams');
});

// Variáveis para o jogo
let gameInterval;
let canvas;
let ctx;
let players = [];
let snitch = { x: 300, y: 200, dx: 5, dy: 3 };
let scores = { team1: 0, team2: 0 };

// Definição dos aros (gols)
const hoops = {
    team1: [
        { x: 50, y: 150, radius: 20 },
        { x: 50, y: 200, radius: 20 },
        { x: 50, y: 250, radius: 20 }
    ],
    team2: [
        { x: 550, y: 150, radius: 20 },
        { x: 550, y: 200, radius: 20 },
        { x: 550, y: 250, radius: 20 }
    ]
};

// Classe para representar um jogador
class Player {
    constructor(x, y, team, role) {
        this.x = x;
        this.y = y;
        this.team = team; // 1 ou 2
        this.role = role;
        this.dx = Math.random() * 4 - 2;
        this.dy = Math.random() * 4 - 2;
        this.color = team === 1 ? '#740001' : '#1a472a'; // Cores Grifinória e Sonserina
    }

    update() {
        // Movimento aleatório
        this.x += this.dx;
        this.y += this.dy;

        // Manter dentro do canvas
        if (this.x < 0 || this.x > canvas.width) this.dx *= -1;
        if (this.y < 0 || this.y > canvas.height) this.dy *= -1;

        // Mudar direção aleatoriamente
        if (Math.random() < 0.02) {
            this.dx = Math.random() * 4 - 2;
            this.dy = Math.random() * 4 - 2;
        }
    }

    draw() {
        // Desenhar o jogador
        ctx.beginPath();
        ctx.arc(this.x, this.y, 10, 0, Math.PI * 2);
        ctx.fillStyle = this.color;
        ctx.fill();
        ctx.strokeStyle = '#000';
        ctx.lineWidth = 1;
        ctx.stroke();
        ctx.closePath();

        // Adicionar símbolo baseado na função do jogador
        ctx.fillStyle = '#FFFFFF';
        ctx.font = '12px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        
        if (this.role === 'seeker') {
            ctx.fillText('S', this.x, this.y); // S para Seeker (Apanhador)
        } else if (this.role === 'chaser') {
            ctx.fillText('C', this.x, this.y); // C para Chaser (Artilheiro)
        }
    }
}

function startMatch() {
    // Mostrar área do jogo
    document.getElementById('game-simulation').style.display = 'block';
    
    // Inicializar canvas
    canvas = document.getElementById('game-canvas');
    ctx = canvas.getContext('2d');

    // Criar jogadores
    players = [
        new Player(100, 200, 1, 'chaser'),
        new Player(150, 150, 1, 'chaser'),
        new Player(200, 100, 1, 'seeker'),
        new Player(400, 200, 2, 'chaser'),
        new Player(450, 250, 2, 'chaser'),
        new Player(500, 300, 2, 'seeker')
    ];

    // Iniciar loop do jogo
    if (gameInterval) clearInterval(gameInterval);
    gameInterval = setInterval(updateGame, 50);

    // Iniciar comentários
    updateCommentary('A partida começou! Os jogadores estão em suas posições!');
}

function updateGame() {
    // Limpar canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Desenhar legenda
    drawLegend();

    // Desenhar os aros
    drawHoops();

    // Atualizar e desenhar jogadores
    players.forEach(player => {
        player.update();
        player.draw();
    });

    // Atualizar e desenhar pomo de ouro
    updateSnitch();
    drawSnitch();

    // Verificar colisões e pontuação
    checkScoring();
}

function drawHoops() {
    // Desenhar aros do time 1 (Grifinória)
    hoops.team1.forEach(hoop => {
        ctx.beginPath();
        ctx.arc(hoop.x, hoop.y, hoop.radius, 0, Math.PI * 2);
        ctx.strokeStyle = '#740001';
        ctx.lineWidth = 3;
        ctx.stroke();
        ctx.closePath();

        // Desenhar a base do aro
        ctx.beginPath();
        ctx.moveTo(hoop.x + hoop.radius, hoop.y);
        ctx.lineTo(hoop.x + hoop.radius + 10, hoop.y);
        ctx.strokeStyle = '#740001';
        ctx.lineWidth = 3;
        ctx.stroke();
        ctx.closePath();
    });

    // Desenhar aros do time 2 (Sonserina)
    hoops.team2.forEach(hoop => {
        ctx.beginPath();
        ctx.arc(hoop.x, hoop.y, hoop.radius, 0, Math.PI * 2);
        ctx.strokeStyle = '#1a472a';
        ctx.lineWidth = 3;
        ctx.stroke();
        ctx.closePath();

        // Desenhar a base do aro
        ctx.beginPath();
        ctx.moveTo(hoop.x - hoop.radius, hoop.y);
        ctx.lineTo(hoop.x - hoop.radius - 10, hoop.y);
        ctx.strokeStyle = '#1a472a';
        ctx.lineWidth = 3;
        ctx.stroke();
        ctx.closePath();
    });
}

function drawSnitch() {
    // Desenhar as asas do pomo
    ctx.beginPath();
    ctx.moveTo(snitch.x - 8, snitch.y);
    ctx.quadraticCurveTo(snitch.x - 15, snitch.y - 10, snitch.x - 20, snitch.y);
    ctx.quadraticCurveTo(snitch.x - 15, snitch.y + 10, snitch.x - 8, snitch.y);
    ctx.fillStyle = '#FFD700';
    ctx.fill();
    ctx.closePath();

    ctx.beginPath();
    ctx.moveTo(snitch.x + 8, snitch.y);
    ctx.quadraticCurveTo(snitch.x + 15, snitch.y - 10, snitch.x + 20, snitch.y);
    ctx.quadraticCurveTo(snitch.x + 15, snitch.y + 10, snitch.x + 8, snitch.y);
    ctx.fillStyle = '#FFD700';
    ctx.fill();
    ctx.closePath();

    // Desenhar o corpo do pomo
    ctx.beginPath();
    ctx.arc(snitch.x, snitch.y, 5, 0, Math.PI * 2);
    ctx.fillStyle = '#FFD700';
    ctx.fill();
    ctx.strokeStyle = '#B8860B';
    ctx.lineWidth = 1;
    ctx.stroke();
    ctx.closePath();
}

function updateSnitch() {
    snitch.x += snitch.dx;
    snitch.y += snitch.dy;

    // Manter pomo dentro do canvas
    if (snitch.x < 20 || snitch.x > canvas.width - 20) snitch.dx *= -1;
    if (snitch.y < 20 || snitch.y > canvas.height - 20) snitch.dy *= -1;

    // Movimento mais errático
    if (Math.random() < 0.08) {
        snitch.dx = Math.random() * 10 - 5;
        snitch.dy = Math.random() * 10 - 5;
    }
}

function checkScoring() {
    players.forEach(player => {
        if (player.role === 'chaser') {
            // Verificar colisão com os aros
            const targetHoops = player.team === 1 ? hoops.team2 : hoops.team1;
            targetHoops.forEach(hoop => {
                const dx = player.x - hoop.x;
                const dy = player.y - hoop.y;
                const distance = Math.sqrt(dx * dx + dy * dy);
                
                if (distance < hoop.radius + 10) {
                    // Gol!
                    if (player.team === 1) {
                        scores.team1 += 10;
                        updateCommentary('Grifinória marca através dos aros! +10 pontos!');
                    } else {
                        scores.team2 += 10;
                        updateCommentary('Sonserina marca através dos aros! +10 pontos!');
                    }
                    document.getElementById('score-team1').textContent = scores.team1;
                    document.getElementById('score-team2').textContent = scores.team2;
                }
            });
        }
        if (player.role === 'seeker') {
            // Verificar se algum apanhador pegou o pomo
            const dx = player.x - snitch.x;
            const dy = player.y - snitch.y;
            const distance = Math.sqrt(dx * dx + dy * dy);
            
            if (distance < 15) {
                // Pomo capturado!
                if (player.team === 1) {
                    scores.team1 += 150;
                    updateCommentary('O apanhador da Grifinória capturou o pomo de ouro! +150 pontos!');
                } else {
                    scores.team2 += 150;
                    updateCommentary('O apanhador da Sonserina capturou o pomo de ouro! +150 pontos!');
                }
                
                // Atualizar placar
                document.getElementById('score-team1').textContent = scores.team1;
                document.getElementById('score-team2').textContent = scores.team2;
                
                // Reiniciar posição do pomo
                snitch.x = canvas.width / 2;
                snitch.y = canvas.height / 2;
            }
        }

        // Gols aleatórios para tornar o jogo mais interessante
        if (Math.random() < 0.02) {
            const team = Math.random() < 0.5 ? 1 : 2;
            if (team === 1) {
                scores.team1 += 10;
                updateCommentary('Grifinória marca! +10 pontos!');
            } else {
                scores.team2 += 10;
                updateCommentary('Sonserina marca! +10 pontos!');
            }
            document.getElementById('score-team1').textContent = scores.team1;
            document.getElementById('score-team2').textContent = scores.team2;
        }
    });
}

function updateCommentary(text) {
    const commentary = document.getElementById('game-commentary');
    commentary.textContent = text;
}

function drawLegend() {
    const legendY = 20;
    const spacing = 120;
    ctx.font = '12px Arial';
    ctx.textAlign = 'left';
    ctx.textBaseline = 'middle';

    // Legenda Grifinória
    ctx.beginPath();
    ctx.arc(50, legendY, 10, 0, Math.PI * 2);
    ctx.fillStyle = '#740001';
    ctx.fill();
    ctx.strokeStyle = '#000';
    ctx.lineWidth = 1;
    ctx.stroke();
    ctx.closePath();
    ctx.fillStyle = '#000';
    ctx.fillText('Grifinória', 70, legendY);

    // Legenda Sonserina
    ctx.beginPath();
    ctx.arc(50 + spacing, legendY, 10, 0, Math.PI * 2);
    ctx.fillStyle = '#1a472a';
    ctx.fill();
    ctx.strokeStyle = '#000';
    ctx.lineWidth = 1;
    ctx.stroke();
    ctx.closePath();
    ctx.fillText('Sonserina', 70 + spacing, legendY);

    // Legenda Pomo de Ouro
    ctx.beginPath();
    ctx.arc(50 + spacing * 2, legendY, 5, 0, Math.PI * 2);
    ctx.fillStyle = '#FFD700';
    ctx.fill();
    ctx.strokeStyle = '#B8860B';
    ctx.lineWidth = 1;
    ctx.stroke();
    ctx.closePath();
    ctx.fillText('Pomo de Ouro', 70 + spacing * 2, legendY);

    // Legenda dos papéis
    const roleY = 40;
    ctx.fillStyle = '#000';
    ctx.fillText('S = Seeker (Apanhador)', 50, roleY);
    ctx.fillText('C = Chaser (Artilheiro)', 200, roleY);
}

// Adicionando feedback visual aos botões
const buttons = document.querySelectorAll('nav button');
buttons.forEach(button => {
    button.addEventListener('click', () => {
        button.classList.add('clicked');
        setTimeout(() => {
            button.classList.remove('clicked');
        }, 300);
    });
});
