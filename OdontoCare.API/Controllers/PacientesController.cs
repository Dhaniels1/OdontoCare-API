
using Microsoft.AspNetCore.Mvc;
using OdontoCare.Application.DTOs;
using OdontoCare.Application.Interfaces;
using OdontoCare.Domain.Entities;

namespace OdontoCare.API.Controllers;

[ApiController]
[Route("api/[controller]")]
public class PacientesController : ControllerBase
{
    private readonly IPacienteService _pacienteService;

    public PacientesController(IPacienteService pacienteService)
    {
        _pacienteService = pacienteService;
    }

    // GET: api/Pacientes
    [HttpGet]
    public async Task<IActionResult> GetTodos()
    {
        var pacientes = await _pacienteService.GetTodosAsync();

        var resposta = pacientes.Select(p => new PacienteResponseDto
        {
            IdPaciente = p.IdPaciente,
            Nome = p.Nome,
            Telefone = p.Telefone,
            Email = p.Email,
            Cidade = p.Cidade,
            Estado = p.Estado
        });

        return Ok(resposta);
    }

    // GET: api/Pacientes/4
    [HttpGet("{id:int}")]
    public async Task<IActionResult> GetPorId(int id)
    {
        var paciente = await _pacienteService.GetPorIdAsync(id);

        if (paciente == null)
        {
            return NotFound(new
            {
                mensagem = "Paciente não encontrado."
            });
        }

        var resposta = new PacienteResponseDto
        {
            IdPaciente = paciente.IdPaciente,
            Nome = paciente.Nome,
            Telefone = paciente.Telefone,
            Email = paciente.Email,
            Cidade = paciente.Cidade,
            Estado = paciente.Estado
        };

        return Ok(resposta);
    }

    // POST: api/Pacientes
    [HttpPost]
    public async Task<IActionResult> Criar(
        [FromBody] PacienteCreateDto dto)
    {
        var cpf = dto.Cpf.Trim();

        var cpfExiste = await _pacienteService.CpfExisteAsync(cpf);

        if (cpfExiste)
        {
            return Conflict(new
            {
                mensagem = "Já existe um paciente cadastrado com esse CPF."
            });
        }

        var paciente = new Paciente
        {
            Nome = dto.Nome.Trim(),
            Cpf = cpf,
            Telefone = dto.Telefone,
            Email = dto.Email,
            Cep = dto.Cep,
            Logradouro = dto.Logradouro,
            Numero = dto.Numero,
            Bairro = dto.Bairro,
            Cidade = dto.Cidade,
            Estado = dto.Estado,
            DataNascimento = dto.DataNascimento
        };

        var criado = await _pacienteService.CriarAsync(paciente);

        var resposta = new PacienteResponseDto
        {
            IdPaciente = criado.IdPaciente,
            Nome = criado.Nome,
            Telefone = criado.Telefone,
            Email = criado.Email,
            Cidade = criado.Cidade,
            Estado = criado.Estado
        };

        return CreatedAtAction(
            nameof(GetPorId),
            new { id = criado.IdPaciente },
            resposta
        );
    }

    // PUT: api/Pacientes/11
    [HttpPut("{id:int}")]
    public async Task<IActionResult> Atualizar(
        int id,
        [FromBody] PacienteUpdateDto dto)
    {
        var paciente = new Paciente
        {
            Nome = dto.Nome.Trim(),
            Telefone = dto.Telefone,
            Email = dto.Email,
            Cep = dto.Cep,
            Logradouro = dto.Logradouro,
            Numero = dto.Numero,
            Bairro = dto.Bairro,
            Cidade = dto.Cidade,
            Estado = dto.Estado,
            DataNascimento = dto.DataNascimento
        };

        var atualizado = await _pacienteService.AtualizarAsync(
            id,
            paciente
        );

        if (!atualizado)
        {
            return NotFound(new
            {
                mensagem = "Paciente não encontrado."
            });
        }

        var resposta = await _pacienteService.GetPorIdAsync(id);

        return Ok(new PacienteResponseDto
        {
            IdPaciente = resposta!.IdPaciente,
            Nome = resposta.Nome,
            Telefone = resposta.Telefone,
            Email = resposta.Email,
            Cidade = resposta.Cidade,
            Estado = resposta.Estado
        });
    }

    // DELETE: api/Pacientes/11
    [HttpDelete("{id:int}")]
    public async Task<IActionResult> Excluir(int id)
    {
        try
        {
            var excluido = await _pacienteService.ExcluirAsync(id);

            if (!excluido)
            {
                return NotFound(new
                {
                    mensagem = "Paciente não encontrado."
                });
            }

            return Ok(new
            {
                mensagem = "Paciente excluído com sucesso."
            });
        }
        catch (InvalidOperationException ex)
        {
            return Conflict(new
            {
                mensagem = ex.Message
            });
        }
    }
}
